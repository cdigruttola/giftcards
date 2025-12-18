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

namespace cdigruttola\GiftCard\Entity;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="cdigruttola\GiftCard\Repository\ProductGiftCardOrderDetailRepository")
 *
 * @ORM\Table()
 */
class ProductGiftCardOrderDetail
{
    /**
     * @var int
     *
     * @ORM\Id
     *
     * @ORM\Column(name="id_order_detail", type="integer")
     *
     * @Orm\GeneratedValue(strategy="NONE")
     */
    private $id_order_detail;

    /**
     * @var int
     *
     * @ORM\Column(name="id_cart_rule", type="integer")
     *
     * @Orm\GeneratedValue(strategy="NONE")
     */
    private $id_cart_rule;

    /**
     * @var int
     *
     * @ORM\Column(name="id_customization", type="integer")
     *
     * @Orm\GeneratedValue(strategy="NONE")
     */
    private $id_customization;

    /**
     * @var bool
     *
     * @ORM\Column(name="mail_sent", type="boolean")
     */
    private $mail_sent;

    /**
     * @return int
     */
    public function getIdOrderDetail(): int
    {
        return $this->id_order_detail;
    }

    /**
     * @param int $id_order_detail
     *
     * @return $this
     */
    public function setIdOrderDetail(int $id_order_detail): self
    {
        $this->id_order_detail = $id_order_detail;

        return $this;
    }

    public function getIdCartRule(): int
    {
        return $this->id_cart_rule;
    }

    public function setIdCartRule(int $id_cart_rule): self
    {
        $this->id_cart_rule = $id_cart_rule;

        return $this;
    }

    public function getIdCustomization(): int
    {
        return $this->id_customization;
    }

    public function setIdCustomization(int $id_customization): self
    {
        $this->id_customization = $id_customization;

        return $this;
    }

    /**
     * @return bool
     */
    public function getMailSent(): bool
    {
        return $this->mail_sent;
    }

    /**
     * @param bool $mail_sent
     *
     * @return $this
     */
    public function setMailSent(bool $mail_sent): self
    {
        $this->mail_sent = $mail_sent;

        return $this;
    }
}
