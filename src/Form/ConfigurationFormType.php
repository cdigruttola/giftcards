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

declare(strict_types=1);

namespace cdigruttola\GiftCard\Form;

if (!defined('_PS_VERSION_')) {
    exit;
}

use cdigruttola\GiftCard\Configuration\GiftCardConfiguration;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatableType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConfigurationFormType extends TranslatorAwareType
{
    /**
     * @var array
     */
    private $taxChoices;

    /**
     * @var array
     */
    private $orderStateChoices;

    /**
     * @param array $taxChoices
     */
    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        array $taxChoices,
        array $orderStateChoices,
    ) {
        parent::__construct($translator, $locales);
        $this->taxChoices = $taxChoices;
        $this->orderStateChoices = $orderStateChoices;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(GiftCardConfiguration::GIFT_CARD_VALIDITY, NumberType::class, [
                'label' => $this->trans('Gift Card Validity Months', 'Modules.Giftcard.Admin'),
                'required' => true,
            ])
            ->add(GiftCardConfiguration::GIFT_CARD_PREFIX_CODE, TextType::class, [
                'label' => $this->trans('Gift Card Prefix', 'Modules.Giftcard.Admin'),
                'required' => true,
            ])
            ->add(GiftCardConfiguration::GIFT_CARD_EMAIL_SUBJECT, TranslatableType::class, [
                'type' => TextType::class,
                'locales' => $this->locales,
                'label' => $this->trans('Gift Card Email Subject', 'Modules.Giftcard.Admin'),
                'required' => true,
            ])
            ->add(GiftCardConfiguration::GIFT_CARD_TAX, ChoiceType::class, [
                'label' => $this->trans('Gift Card Tax', 'Modules.Giftcard.Admin'),
                'required' => true,
                'choices' => $this->taxChoices,
            ])
            ->add(GiftCardConfiguration::GIFT_CARD_PARTIAL_USE, SwitchType::class, [
                'label' => $this->trans('Gift Card Partial use', 'Modules.Giftcard.Admin'),
            ])
            ->add(GiftCardConfiguration::GIFT_CARD_CART_RULE_COMBINATION, SwitchType::class, [
                'label' => $this->trans('Allow using Gift Card with other cart rules', 'Modules.Giftcard.Admin'),
            ])
            ->add(GiftCardConfiguration::GIFT_CARD_CART_RULE_BUY, SwitchType::class, [
                'label' => $this->trans('Allow buying Gift Card with other Gift Cards', 'Modules.Giftcard.Admin'),
            ])
            ->add(GiftCardConfiguration::GIFT_CARD_TO_SEND_ORDER_STATE, ChoiceType::class, [
            'label' => $this->trans('Order Status used to send email with Gift Card', 'Modules.Giftcard.Admin'),
            'required' => true,
            'choices' => $this->orderStateChoices,
        ]);
    }
}
