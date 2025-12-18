<?php
/**
 * Copyright since 2007 Carmine Di Gruttola
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    cdigruttola <c.digruttola@hotmail.it>
 * @copyright Copyright since 2007 Carmine Di Gruttola
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use cdigruttola\GiftCard\Configuration\GiftCardConfiguration;
use cdigruttola\GiftCard\Entity\ProductGiftCard;
use cdigruttola\GiftCard\Entity\ProductGiftCardOrderDetail;
use cdigruttola\GiftCard\Form\DataHandler\ProductGiftCardFormDataHandler;
use cdigruttola\GiftCard\Form\ProductGiftCardType;
use cdigruttola\GiftCard\Repository\ProductGiftCardOrderDetailRepository;
use cdigruttola\GiftCard\Repository\ProductGiftCardRepository;
use cdigruttola\VirtualCombinations\Entity\ProductVirtualCombinations;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Adapter\CartRule\LegacyDiscountApplicationType;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductType;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataProvider\FormDataProviderInterface;
use PrestaShop\PrestaShop\Core\MailTemplate\Layout\Layout;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeCatalogInterface;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeCollectionInterface;
use PrestaShop\PrestaShop\Core\MailTemplate\ThemeInterface;

class GiftCards extends Module
{
    public function __construct()
    {
        $this->name = 'giftcards';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'cdigruttola';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.2', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->trans('Gift Card', [], 'Modules.Giftcard.Main');
        $this->description = $this->trans('This module helps you to manage gift card', [], 'Modules.Giftcard.Main');
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install()
    {
        include dirname(__FILE__) . '/sql/install.php';

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (parent::install()
            && $this->registerHook('actionProductFormBuilderModifier')
            && $this->registerHook('actionProductFormDataProviderData')
            && $this->registerHook('actionAfterUpdateProductFormHandler')
            && $this->registerHook('actionOrderStatusPostUpdate')
            && $this->registerHook(ThemeCatalogInterface::LIST_MAIL_THEMES_HOOK)
        ) {
            $this->createTax();
            $this->createCategory();
            $this->createAttributes();
            $this->createOrderState();
            $this->createProduct();
            return true;
        }
        return false;
    }

    public function uninstall()
    {
        //$this->id = 109;
        include dirname(__FILE__) . '/sql/uninstall.php';
        return parent::uninstall();
    }

    public function getContent()
    {
        Tools::redirectAdmin(SymfonyContainer::getInstance()->get('router')->generate('giftcard_config_controller'));
    }

    public function createTax()
    {
        if (Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_TAX)) {
            $object = new Tax((int)Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_TAX));

            if (Validate::isLoadedObject($object)) {
                return;
            }
        }

        $tax = new Tax();
        $tax->name = [];
        foreach (Language::getLanguages() as $lang) {
            $tax->name[$lang['id_lang']] = $this->trans('Gift card 0%', [], 'Modules.Giftcard.Main', $lang['locale']);
        }
        $tax->rate = 0.00;
        $tax->active = true;
        $tax->save();

        $taxRulesGroup = new TaxRulesGroup();
        foreach (Language::getLanguages() as $lang) {
            $taxRulesGroup->name = $this->trans('Gift card (0%)', [], 'Modules.Giftcard.Main', $lang['locale']);
        }
        $taxRulesGroup->active = true;
        $taxRulesGroup->save();

        $countries = Country::getCountries($this->context->language->id);
        $selected_countries = [];
        foreach ($countries as $country) {
            $selected_countries[] = (int)$country['id_country'];
        }

        foreach ($selected_countries as $id_country) {
            $taxRule = new TaxRule();
            $taxRule->id_tax = $tax->id;
            $taxRule->id_country = $id_country;
            $taxRule->id_tax_rules_group = $taxRulesGroup->id;
            $taxRule->save();
        }

        Configuration::updateGlobalValue(GiftCardConfiguration::GIFT_CARD_TAX, $tax->id);
        unset($tax, $taxRule, $taxRulesGroup);
    }

    public function createCategory()
    {
        if (Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_CATEGORY)) {
            $object = new Category((int)Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_CATEGORY));

            if (Validate::isLoadedObject($object)) {
                return;
            }
        }

        $category = new Category();
        $languages = Language::getLanguages();

        $shops = Shop::getShops(true, null, true);
        foreach ($languages as $language) {
            $category->name[(int)$language['id_lang']] = $this->trans('Gift cards', [], 'Modules.Giftcard.Main', $language['locale']);
            $category->link_rewrite[(int)$language['id_lang']] = $this->trans('gift-cards', [], 'Modules.Giftcard.Main', $language['locale']);
        }
        $category->id_parent = (int)Configuration::get('PS_HOME_CATEGORY');
        $category->is_root_category = false;
        $category->level_depth = $category->id_parent + 1;
        $category->active = true;
        $category->id_shop_list = $shops;
        if ($category->save()) {
            Configuration::updateGlobalValue(GiftCardConfiguration::GIFT_CARD_CATEGORY, $category->id);
        }
    }

    public function createAttributes()
    {
        if (!Customization::isFeatureActive()) {
            Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', '1');
        }

        if (Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_ATTRIBUTE_AMOUNT)) {
            $attributeGroup = new AttributeGroup((int)Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_ATTRIBUTE_AMOUNT));

            if (Validate::isLoadedObject($attributeGroup)) {
                return;
            }
        }

        $languages = Language::getLanguages();
        $shops = Shop::getShops(true, null, true);
        $attribute_group_obj = new AttributeGroup();
        foreach ($languages as $language) {
            $attribute_group_obj->name[(int)$language['id_lang']] = $this->trans('Amount', [], 'Modules.Giftcard.Main', $language['locale']);
            $attribute_group_obj->public_name[(int)$language['id_lang']] = $this->trans('Amount', [], 'Modules.Giftcard.Main', $language['locale']);
            $attribute_group_obj->group_type = 'select';
            $attribute_group_obj->id_shop_list = $shops;
        }
        if ($attribute_group_obj->save()) {
            Configuration::updateGlobalValue(GiftCardConfiguration::GIFT_CARD_ATTRIBUTE_AMOUNT, $attribute_group_obj->id);

            $list_add = ['10 €', '20 €', '50 €'];
            foreach ($list_add as $amount) {
                $obj = new ProductAttribute();
                $obj->id_attribute_group = (int)$attribute_group_obj->id;
                foreach ($languages as $language) {
                    $obj->name[(int)$language['id_lang']] = $amount;
                }
                $obj->position = ProductAttribute::getHigherPosition((int)$attribute_group_obj->id) + 1;
                $obj->id_shop_list = $shops;
                $obj->save();
                $obj->cleanPositions((int)$attribute_group_obj->id, false);
            }
        }
    }

    public function createOrderState()
    {
        if (Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_SENT_ORDER_STATE)) {
            $orderState = new OrderState((int)Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_SENT_ORDER_STATE));

            if (Validate::isLoadedObject($orderState) && $this->name === $orderState->module_name) {
                return;
            }
        }

        $orderState = new OrderState();
        $orderState->module_name = $this->name;
        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $orderState->name[(int)$language['id_lang']] = $this->trans('Gift Card Sent', [], 'Modules.Giftcard.Main', $language['locale']);
        }
        $orderState->color = '#01B887';
        $orderState->logable = true;
        $orderState->paid = true;
        $orderState->invoice = true;
        $orderState->shipped = true;
        $orderState->delivery = false;
        $orderState->pdf_delivery = false;
        $orderState->pdf_invoice = false;
        $orderState->send_email = false;
        $orderState->hidden = false;
        $orderState->unremovable = true;
        $orderState->template = 'send_gift_card';
        $orderState->deleted = false;
        $orderState->save();

        Configuration::updateGlobalValue(GiftCardConfiguration::GIFT_CARD_SENT_ORDER_STATE, (int)$orderState->id);

        $sourceFile = _PS_MODULE_DIR_ . $this->name . '/views/img/email_sent.gif';
        $destinationFile = _PS_IMG_DIR_ . 'os/' . $orderState->id . '.gif';
        copy($sourceFile, $destinationFile);
        $resource = _PS_IMG_DIR_ . 'os/' . $orderState->id . '.gif';
        $filename = _PS_TMP_IMG_DIR_ . 'order_state_mini_' . $orderState->id . '_' . $this->context->shop->id . '.gif';
        copy($resource, $filename);
    }

    public function createProduct()
    {
        if (Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_PRODUCT)) {
            $product = new Product((int)Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_PRODUCT));

            if (Validate::isLoadedObject($product)) {
                return;
            }
        }

        $product = new Product();
        $product->is_virtual = true;
        foreach (Language::getLanguages() as $language) {
            $product->name[$language['id_lang']] = $this->trans('Gift Card', [], 'Modules.Giftcard.Main', $language['locale']);
            $product->link_rewrite[$language['id_lang']] = $this->trans('gift-card', [], 'Modules.Giftcard.Main', $language['locale']);
        }
        $product->reference = 'GC';
        $product->id_tax_rules_group = Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_TAX);

        $customizationLabels = [];
        foreach (Language::getLanguages() as $language) {
            $gift_card_labels = [
                'FROM' => $this->trans('From', [], 'Modules.Giftcard.Main'),
                'TO' => $this->trans('To', [], 'Modules.Giftcard.Main'),
                'MESSAGE' => $this->trans('Message', [], 'Modules.Giftcard.Main'),
                'EMAIL' => $this->trans('Recipient Email', [], 'Modules.Giftcard.Main'),
            ];
            $customizationLabels[$language['id_lang']] = $gift_card_labels;
        }

        $product->text_fields = 4;
        $product->customizable = 1;
        $product->save();
        $product->updateCategories([(int)Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_CATEGORY)]);

        Configuration::updateGlobalValue(GiftCardConfiguration::GIFT_CARD_PRODUCT, $product->id);

        if (!Combination::isFeatureActive()) {
            Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', true);
        }

        foreach ($gift_card_labels as $key => $label) {
            $field = new CustomizationField();
            $field->id_product = $product->id;
            $field->type = Product::CUSTOMIZE_TEXTFIELD;
            $field->required = true;
            foreach (Language::getLanguages() as $language) {
                $field->name[$language['id_lang']] = $customizationLabels[$language['id_lang']][$key];
            }
            $field->save();
            Configuration::updateGlobalValue('GIFT_CARD_EMAIL_' . $key, $field->id);
        }

        $entity = new ProductGiftCard();
        $entity->setActive(true);
        $entity->setIdProduct($product->id);

        $virtual = new ProductVirtualCombinations();
        $virtual->setActive(true);
        $virtual->setIdProduct($product->id);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        $entityManager->persist($entity);
        $entityManager->persist($virtual);
        $entityManager->flush();

        Db::getInstance()->update('product', ['product_type' => ProductType::TYPE_COMBINATIONS], 'id_product = ' . $product->id);
        Db::getInstance()->update('product', ['is_virtual' => 1], 'id_product = ' . $product->id);
    }

    public function hookActionAfterUpdateProductFormHandler($params)
    {
        $form_data = $params['form_data']['options']['gift_card'];

        /** @var ProductGiftCardFormDataHandler $handler */
        $handler = $this->get('cdigruttola.gift_card.form.identifiable_object.data_handler.product_form_data_handler');
        if (!empty($form_data)) {
            $handler->createOrUpdate($form_data);
        }
    }

    public function hookActionProductFormBuilderModifier($params)
    {
        $productId = (int)$params['id'];
        $product = new \Product($productId);
        if (Validate::isLoadedObject($product) && $product->is_virtual) {
            $formBuilder = $params['form_builder']->get('options');
            $formBuilder->add(
                'gift_card',
                ProductGiftCardType::class, [
                    'label' => $this->trans('Is Gift Card?', [], 'Modules.Giftcard.Main'),
                ]
            );
        }
    }

    public function hookActionProductFormDataProviderData($params)
    {
        /** @var FormDataProviderInterface $formDataProvider */
        $formDataProvider = $this->get('cdigruttola.gift_card.form.identifiable_object.data_provider.product_data_provider');
        $params['data']['options']['gift_card'] = $formDataProvider->getData($params['id']);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        if (!Validate::isLoadedObject($order = new Order($params['id_order']))
            || !($params['newOrderStatus'] instanceof OrderState)
            || 1 != $params['newOrderStatus']->paid
            || Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_TO_SEND_ORDER_STATE) != $params['newOrderStatus']->id
            || !Validate::isLoadedObject($customer = new Customer($order->id_customer))
            || !($giftCardProducts = $this->getGiftCardProducts($order))
        ) {
            return;
        }

        foreach ($giftCardProducts as $data) {
            /** @var ProductGiftCardOrderDetailRepository $repository */
            $repository = $this->get('cdigruttola.gift_card.repository.product_gift_card_order_detail');
            /** @var ProductGiftCardOrderDetail|null $entity */
            $entity = $repository->findOneBy(['id_order_detail' => $data['id_order_detail']]);
            if ($entity === null) {
                $cart_rule = new CartRule();
                foreach (Language::getLanguages(false) as $language) {
                    $cart_rule->name[(int)$language['id_lang']] = $this->trans('Gift Card', [], 'Modules.Giftcard.Main', $language['locale']);
                }
                $cart_rule->quantity = $data['product_quantity'];
                $cart_rule->quantity_per_user = $data['product_quantity'];
                $cart_rule->description = 'Order ' . (int)$order->id;
                $cart_rule->code = Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_PREFIX_CODE) . Tools::strtoupper(Tools::passwdGen(12));
                $cart_rule->date_from = $order->date_add;
                $cart_rule->date_to = date('Y-m-d', strtotime('+' . Configuration::get(GiftCardConfiguration::GIFT_CARD_VALIDITY) . ' month', strtotime($cart_rule->date_from)));
                $cart_rule->reduction_amount = $data['total_price_tax_incl'];
                $cart_rule->reduction_tax = true;
                $cart_rule->free_shipping = false;
                $cart_rule->reduction_currency = $order->id_currency;
                $cart_rule->partial_use = (bool)Configuration::get(GiftCardConfiguration::GIFT_CARD_PARTIAL_USE);
                $cart_rule->product_restriction = 1;
                $cart_rule->cart_rule_restriction = 0;
                $cart_rule->reduction_product = LegacyDiscountApplicationType::ORDER_WITHOUT_SHIPPING;
                $cart_rule->shop_restriction = Shop::isFeatureActive() ? true : false;
                $cart_rule->save();

                if (Shop::isFeatureActive()) {
                    Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_shop` (`id_cart_rule`, `id_shop`)
                    VALUES(' . (int)$cart_rule->id . ', ' . (int)$order->id_shop . ')');
                }

                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule_group` (`id_cart_rule`, `quantity`)
                    VALUES(' . (int)$cart_rule->id . ', 1)');
                $id_product_rule_group = Db::getInstance()->Insert_ID();

                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule` (`id_product_rule_group`, `type`)
						VALUES (' . (int)$id_product_rule_group . ', \'categories\')');
                $id_product_rule = Db::getInstance()->Insert_ID();

                $values = [];

                $categories = Category::getCategories($this->context->language->id, true, false);
                foreach ($categories as $category) {
                    if (Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_CATEGORY) !== $category['id_category']) {
                        $values[] = '(' . (int)$id_product_rule . ',' . (int)$category['id_category'] . ')';
                    }
                }

                $values = array_unique($values);
                if (count($values)) {
                    Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_product_rule_value` (`id_product_rule`, `id_item`) VALUES ' . implode(',', $values));
                }

                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_combination` (`id_cart_rule_1`, `id_cart_rule_2`) (
                SELECT id_cart_rule, ' . (int)$cart_rule->id . ' FROM `' . _DB_PREFIX_ . 'cart_rule` WHERE cart_rule_restriction = 1 )');

                $entity = new ProductGiftCardOrderDetail();
                $entity->setIdOrderDetail($data['id_order_detail']);
                $entity->setIdCartRule($cart_rule->id);
                $entity->setIdCustomization((int)$data['id_customization']);
                $entity->setMailSent(false);

                /** @var EntityManagerInterface $entityManager */
                $entityManager = $this->get('doctrine.orm.default_entity_manager');
                $entityManager->persist($entity);
                $entityManager->flush();

                if ($this->sendEmail($order->id_lang, $cart_rule, $data, (int)$order->id_shop)) {
                    if (count($giftCardProducts) === count($order->getProducts())) {
                        $order->setCurrentState(Configuration::get(GiftCardConfiguration::GIFT_CARD_SENT_ORDER_STATE));
                    }
                    $entity->setMailSent(true);
                    $entityManager->flush();
                }
            }
        }
    }

    public function sendEmail($id_lang, $cart_rule, $productData, $id_shop = null)
    {
        $template_vars = [
            '{gift_card_amount}' => Tools::getContextLocale($this->context)->formatPrice($cart_rule->reduction_amount, Currency::getIsoCodeById($cart_rule->reduction_currency)),
            '{gift_card_code}' => $cart_rule->code,
            '{gift_card_expiration}' => Tools::displayDate($cart_rule->date_to),
        ];

        $customizedDatas = $productData['customizedDatas'];

        if (empty($customizedDatas)) {
            return false;
        }

        $customizations = $customizedDatas[(int)$productData['id_address_delivery']][(int)$productData['id_customization']]['datas'][Product::CUSTOMIZE_TEXTFIELD];
        foreach ($customizations as $customization) {
            if ($customization['index'] == Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_EMAIL_FROM)) {
                $sender = $customization['value'];
            }
            if ($customization['index'] == Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_EMAIL_TO)) {
                $name = $customization['value'];
            }
            if ($customization['index'] == Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_EMAIL_MESSAGE)) {
                $message = $customization['value'];
            }
            if ($customization['index'] == Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_EMAIL_EMAIL)) {
                $email = $customization['value'];
            }
        }

        if (!isset($email) || !isset($message) || !isset($name) || !isset($sender)) {
            return false;
        }
        $template_vars = array_merge($template_vars, [
            '{name}' => $name,
            '{gift_card_message}' => $message,
            '{gift_card_sender}' => $sender,
        ]);

        $mail_iso = Language::getIsoById($id_lang);

        $dir_mail = false;
        if (file_exists(_PS_MODULE_DIR_ . $this->name . '/mails/' . $mail_iso . '/send_gift_card.txt')
            && file_exists(_PS_MODULE_DIR_ . $this->name . '/mails/' . $mail_iso . '/send_gift_card.html')) {
            $dir_mail = _PS_MODULE_DIR_ . $this->name . '/mails/';
        }

        if (file_exists(_PS_MAIL_DIR_ . $mail_iso . '/send_gift_card.txt')
            && file_exists(_PS_MAIL_DIR_ . $mail_iso . '/send_gift_card.html')) {
            $dir_mail = _PS_MAIL_DIR_;
        }

        if (!$dir_mail) {
            $mail_iso = 'en';
            $dir_mail = _PS_MODULE_DIR_ . $this->name . '/mails/';
        }

        if (Mail::Send(
            Language::getIdByIso($mail_iso),
            'send_gift_card',
            Configuration::get(GiftCardConfiguration::GIFT_CARD_EMAIL_SUBJECT, $id_lang),
            $template_vars,
            $email,
            $name,
            null,
            null,
            null,
            null,
            $dir_mail,
            false,
            $id_shop
        )) {
            return true;
        }

        return false;
    }


    public function hookActionListMailThemes($params)
    {
        if (!isset($params['mailThemes'])) {
            return;
        }

        //Add the module theme called example_module_theme
        /** @var ThemeCollectionInterface $themes */
        $themes = $params['mailThemes'];

        /** @var ThemeInterface $theme */
        foreach ($themes as $theme) {
            $theme->getLayouts()->add(new Layout(
                'send_gift_card',
                '@Modules/giftcards/mails/send_gift_card_' . $theme->getName() . '.html.twig',
                '',
                $this->name
            ));
        }
    }

    /**
     * @param Order $order
     * @throws Exception
     */
    private function getGiftCardProducts($order): array
    {
        $products = $order->getProducts();
        $giftCards = [];
        foreach ($products as $product) {
            /** @var ProductGiftCardRepository $repository */
            $repository = $this->get('cdigruttola.gift_card.repository.product_gift_card');
            /** @var ProductGiftCard|null $entity */
            $entity = $repository->findOneBy(['id_product' => $product['id_product'], 'active' => true]);
            if ($entity === null) {
                continue;
            }
            $giftCards[] = $product;
        }
        return $giftCards;
    }
}
