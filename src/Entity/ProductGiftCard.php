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
 * @ORM\Entity(repositoryClass="cdigruttola\GiftCard\Repository\ProductGiftCardRepository")
 *
 * @ORM\Table()
 */
class ProductGiftCard
{
    /**
     * @var int
     *
     * @ORM\Id
     *
     * @ORM\Column(name="id_product", type="integer")
     *
     * @Orm\GeneratedValue(strategy="NONE")
     */
    private $id_product;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @return int
     */
    public function getIdProduct(): int
    {
        return $this->id_product;
    }

    /**
     * @param int $id_product
     *
     * @return $this
     */
    public function setIdProduct(int $id_product): self
    {
        $this->id_product = $id_product;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
