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
use Shopware\Models\Attribute\Customer as CustomerAttribute;

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
     * This field is used as a cache for the customer object of the currently logged in user.
     *
     * @var Customer
     */
    private $cachedCustomer;

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
        if ($this->cachedCustomer) {
            return $this->cachedCustomer;
        }

        $result = null;
        $customerId = $this->getSession()->sUserId;
        if (!empty($customerId)) {
            /** @var Customer|null $result */
            $result = $this->getRepository()->find($customerId);
        }
        return $result;
    }

    /**
     * Removes the stripeId from the customer (attributes)
     *
     * @param Customer $customer
     */
    public function removeStripeId($customer) {
        $customer->getAttribute()->setStripeCustomerId(null);
        $this->entityManager->flush($customer->getAttribute());
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    public function isEnabled($customer)
    {
        return $customer->getAccountMode() === 1;
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    public function isDisabled($customer)
    {
        return $customer->getAccountMode() === 0;
    }


    /**
     * Ensure that customer has attribute object, if not create one and save it
     *
     * @param Customer $customer
     */
    public function ensureHasAttribute($customer)
    {
        // Make sure the customer has attributes
        if ($customer->getAttribute() === null) {
            $customerAttribute = new CustomerAttribute();
            $customerAttribute->setCustomer($customer);
            $customer->setAttribute($customerAttribute);
            $this->entityManager->persist($customerAttribute);
            $this->entityManager->flush($customerAttribute);
            $this->entityManager->flush($customer);
        }
    }

    /**
     * @param Customer $customer
     * @return string
     */
    public function getName($customer)
    {
        $defaultBillingAddress = $customer->getDefaultBillingAddress();

        // Check for company
        $company = $defaultBillingAddress->getCompany();
        if (!empty($company)) {
            return $company;
        }

        // Use first and last name
        return trim($defaultBillingAddress->getFirstName() . ' ' . $defaultBillingAddress->getLastName());
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    public function hasStripeId($customer)
    {
        $result = true;
        if ($customer->getAttribute() === null ||
            $customer->getAttribute()->getStripeCustomerId() === null) {
            $result = false;
        }
        return $result;
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    public function hasNotStripeId($customer)
    {
        return !$this->hasStripeId($customer);
    }

    /**
     * @param Customer $customer
     * @param string $stripeId
     */
    public function addStripeId($customer, $stripeId)
    {
        $customer->getAttribute()->setStripeCustomerId($stripeId);
        $this->entityManager->flush($customer->getAttribute());
    }

}
