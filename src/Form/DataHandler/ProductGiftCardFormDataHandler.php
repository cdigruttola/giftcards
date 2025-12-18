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

namespace cdigruttola\GiftCard\Form\DataHandler;

if (!defined('_PS_VERSION_')) {
    exit;
}

use cdigruttola\GiftCard\Entity\ProductGiftCard;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;

class ProductGiftCardFormDataHandler implements FormDataHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function createOrUpdate($data): int
    {
        $id = $data['id_product'];
        /** @var ProductGiftCard|null $entity */
        $entity = $this->entityManager->getRepository(ProductGiftCard::class)->findOneBy(['id_product' => $id]);
        if ($entity === null) {
            return $this->create($data);
        }
        return $this->update($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): int
    {
        $entity = new ProductGiftCard();

        $entity->setActive($data['active'] ?? false);
        $entity->setIdProduct((int) $data['id_product']);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity->getIdProduct();
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data): int
    {
        /** @var ProductGiftCard $entity */
        $entity = $this->entityManager->getRepository(ProductGiftCard::class)->findOneBy(['id_product' => $id]);
        $entity->setActive($data['active']);
        $this->entityManager->flush();

        return $entity->getIdProduct();
    }
}
