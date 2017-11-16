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

use Shopware\Models\Customer\Customer;

/**
 * Interface CustomerServiceInterface
 * @package Shopware\Plugins\StripePayment\Service
 */
interface CustomerServiceInterface
{
    /**
     * Returns the current loggedIn Customer or null if not logged in
     *
     * @return Customer|null
     */
    public function getCurrent();

    /**
     * Removes the stripeId from the customer (attributes)
     *
     * @param Customer $customer
     */
    public function removeStripeId($customer);

    /**
     * Ensure that customer has attribute object, if not create one and save it
     *
     * @param Customer $customer
     */
    public function ensureHasAttribute($customer);

    /**
     * @param Customer $customer
     * @return bool
     */
    public function isEnabled($customer);

    /**
     * @param Customer $customer
     * @return bool
     */
    public function isDisabled($customer);

    /**
     * @return string The customers company name, if it exists. Otherwise their joined first and last name
     */
    public function getName($customer);

    /**
     * @param Customer $customer
     * @return bool
     */
    public function hasStripeId($customer);

    /**
     * @param Customer $customer
     * @return bool
     */
    public function hasNotStripeId($customer);

    /**
     * @param Customer $customer
     * @param string $stripeId
     */
    public function addStripeId($customer, $stripeId);
}
