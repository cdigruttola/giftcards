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

namespace cdigruttola\GiftCard\Configuration;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GiftCardConfiguration implements DataConfigurationInterface
{
    public const GIFT_CARD_VALIDITY = 'GIFT_CARD_VALIDITY';
    public const GIFT_CARD_PREFIX_CODE = 'GIFT_CARD_PREFIX_CODE';
    public const GIFT_CARD_TAX = 'GIFT_CARD_TAX';
    public const GIFT_CARD_PARTIAL_USE = 'GIFT_CARD_PARTIAL_USE';
    public const GIFT_CARD_CATEGORY = 'GIFT_CARD_CATEGORY';
    public const GIFT_CARD_ATTRIBUTE_AMOUNT = 'GIFT_CARD_ATTRIBUTE_AMOUNT';
    public const GIFT_CARD_CART_RULE_COMBINATION = 'GIFT_CARD_CART_RULE_COMBINATION';
    public const GIFT_CARD_CART_RULE_BUY = 'GIFT_CARD_CART_RULE_BUY';
    public const GIFT_CARD_TO_SEND_ORDER_STATE = 'GIFT_CARD_TO_SEND_ORDER_STATE';
    public const GIFT_CARD_SENT_ORDER_STATE = 'GIFT_CARD_SENT_ORDER_STATE';
    public const GIFT_CARD_PRODUCT = 'GIFT_CARD_PRODUCT';
    public const GIFT_CARD_EMAIL_SUBJECT = 'GIFT_CARD_EMAIL_SUBJECT';
    public const GIFT_CARD_EMAIL_FROM = 'GIFT_CARD_EMAIL_FROM';
    public const GIFT_CARD_EMAIL_TO = 'GIFT_CARD_EMAIL_TO';
    public const GIFT_CARD_EMAIL_MESSAGE = 'GIFT_CARD_EMAIL_MESSAGE';
    public const GIFT_CARD_EMAIL_EMAIL = 'GIFT_CARD_EMAIL_EMAIL';

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        $return = [];

        $return[self::GIFT_CARD_VALIDITY] = $this->configuration->get(static::GIFT_CARD_VALIDITY);
        $return[self::GIFT_CARD_PREFIX_CODE] = $this->configuration->get(static::GIFT_CARD_PREFIX_CODE);
        $return[self::GIFT_CARD_TAX] = $this->configuration->get(static::GIFT_CARD_TAX);
        $return[self::GIFT_CARD_PARTIAL_USE] = $this->configuration->get(static::GIFT_CARD_PARTIAL_USE);
        $return[self::GIFT_CARD_CART_RULE_COMBINATION] = $this->configuration->get(static::GIFT_CARD_CART_RULE_COMBINATION);
        $return[self::GIFT_CARD_CART_RULE_BUY] = $this->configuration->get(static::GIFT_CARD_CART_RULE_BUY);
        $return[self::GIFT_CARD_TO_SEND_ORDER_STATE] = $this->configuration->get(static::GIFT_CARD_TO_SEND_ORDER_STATE);
        $return[self::GIFT_CARD_EMAIL_SUBJECT] = $this->configuration->get(static::GIFT_CARD_EMAIL_SUBJECT);

        return $return;
    }

    public function updateConfiguration(array $configuration): array
    {
        $this->configuration->set(self::GIFT_CARD_VALIDITY, $configuration[self::GIFT_CARD_VALIDITY]);
        $this->configuration->set(self::GIFT_CARD_PREFIX_CODE, $configuration[self::GIFT_CARD_PREFIX_CODE]);
        $this->configuration->set(self::GIFT_CARD_TAX, $configuration[self::GIFT_CARD_TAX]);
        $this->configuration->set(self::GIFT_CARD_PARTIAL_USE, $configuration[self::GIFT_CARD_PARTIAL_USE]);
        $this->configuration->set(self::GIFT_CARD_CART_RULE_COMBINATION, $configuration[self::GIFT_CARD_CART_RULE_COMBINATION]);
        $this->configuration->set(self::GIFT_CARD_CART_RULE_BUY, $configuration[self::GIFT_CARD_CART_RULE_BUY]);
        $this->configuration->set(self::GIFT_CARD_TO_SEND_ORDER_STATE, $configuration[self::GIFT_CARD_TO_SEND_ORDER_STATE]);
        $this->configuration->set(self::GIFT_CARD_EMAIL_SUBJECT, $configuration[self::GIFT_CARD_EMAIL_SUBJECT]);

        return [];
    }

    /**
     * Ensure the parameters passed are valid.
     *
     * @return bool Returns true if no exception are thrown
     */
    public function validateConfiguration(array $configuration): bool
    {
        return true;
    }
}