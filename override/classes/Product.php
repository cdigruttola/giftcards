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

use cdigruttola\GiftCard\Configuration\GiftCardConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class Product extends ProductCore
{
    public static function priceCalculation($id_shop, $id_product, $id_product_attribute, $id_country, $id_state, $zipcode, $id_currency, $id_group, $quantity, $use_tax, $decimals, $only_reduc, $use_reduc, $with_ecotax, &$specific_price, $use_group_reduction, $id_customer = 0, $use_customer_price = true, $id_cart = 0, $real_quantity = 0, $id_customization = 0)
    {
        if (Configuration::getGlobalValue(GiftCardConfiguration::GIFT_CARD_PRODUCT) == $id_product) {
            $use_group_reduction = false;
        }

        return parent::priceCalculation($id_shop, $id_product, $id_product_attribute, $id_country, $id_state, $zipcode, $id_currency, $id_group, $quantity, $use_tax, $decimals, $only_reduc, $use_reduc, $with_ecotax, $specific_price, $use_group_reduction, $id_customer, $use_customer_price, $id_cart, $real_quantity, $id_customization);
    }

}