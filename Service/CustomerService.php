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
use Enlight_Components_Session_Namespace;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Repository;

/**
 * Class CustomerService
 * @package Shopware\Plugins\StripePayment\Service
 */
class CustomerService implements CustomerServiceInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * CustomerService constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Repository|EntityRepository
     */
    protected function getRepository() {
       return $this->entityManager->getRepository('Shopware\Models\Customer\Customer');
    }

    /**
     * @return Enlight_Components_Session_Namespace
     */
    protected function getSession()
    {
        return Shopware()->Session();
    }

    /**
     * Returns the current loggedIn Customer or null if not logged in
     *
     * @return Customer|null
     */
    public function getCurrent()
    {
        $result = null;
        $customerId = $this->getSession()->sUserId;

        if (!empty($customerId)) {
            /** @var Customer|null $result */
            $result = $this->getRepository()->find($customerId);
        }
        return $result;
    }

}
