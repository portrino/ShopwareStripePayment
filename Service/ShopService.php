<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Plugins\StripePayment\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Shopware\Models\Shop\DetachedShop;
use Shopware\Models\Shop\Repository;

/**
 * Class ShopService
 * @package Shopware\Plugins\StripePayment\Service
 */
class ShopService implements ShopServiceInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * ShopService constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Repository|EntityRepository
     */
    protected function getRepository()
    {
        return $this->entityManager->getRepository('Shopware\Models\Shop\Shop');
    }

    /**
     * @return DetachedShop
     */
    public function getActiveDefault()
    {
        return $this->getRepository()->getActiveDefault();
    }

}
