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

use Enlight_Plugin_PluginManager;
use Exception;
use Shopware\Models\Customer\Customer as ShopwareCustomer;
use Shopware_Components_Config;
use Shopware_Plugins_Frontend_StripePayment_Bootstrap;
use Stripe\Customer as StripeCustomer;
use Stripe\Stripe;

/**
 * Class StripeService
 * @package Shopware\Plugins\StripePayment\Service
 */
class StripeService implements StripeServiceInterface
{
    /**
     * @var CustomerServiceInterface
     */
    protected $customerService;

    /**
     * @var ShopServiceInterface
     */
    protected $shopService;

    /**
     * @var Shopware_Components_Config
     */
    protected $config;

    /**
     * @var Enlight_Plugin_PluginManager
     */
    protected $pluginManager;

    /**
     * This field is used as a cache for the Stripe customer object of the currently logged in user.
     *
     * @var StripeCustomer
     */
    private $cachedStripeCustomer;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiVersion;

    /**
     * @var string
     */
    protected $pluginVersion;

    /**
     * @var string
     */
    protected $appName;

    /**
     * StripeService constructor.
     * @param CustomerServiceInterface $customerService
     * @param ShopServiceInterface $shopService
     * @param Shopware_Components_Config $config
     * @param Enlight_Plugin_PluginManager $pluginManager
     * @param string $apiVersion
     */
    public function __construct(
        CustomerServiceInterface $customerService,
        ShopServiceInterface $shopService,
        Shopware_Components_Config $config,
        Enlight_Plugin_PluginManager $pluginManager,
        $apiVersion
    ) {
        $this->customerService = $customerService;
        $this->shopService = $shopService;
        $this->config = $config;
        $this->pluginManager = $pluginManager;
        $this->apiVersion = $apiVersion;

        $this->initializeApi();
    }

    /**
     *
     */
    protected function initializeApi()
    {
        $this->apiKey = $this->config->getByNamespace('StripePayment', 'stripeSecretKey');
        $this->appName = $this->config->getByNamespace('StripePayment', 'stripeAppName');
        /** @var Shopware_Plugins_Frontend_StripePayment_Bootstrap $bootstrap */
        $bootstrap = $this->pluginManager->get('Frontend')->get('StripePayment');
        $this->pluginVersion = $bootstrap->getVersion();
        $defaultShop = $this->shopService->getActiveDefault();

        Stripe::setApiKey($this->apiKey);
        Stripe::setApiVersion($this->apiVersion);

        // Set some plugin info that will be added to every Stripe request
        Stripe::setAppInfo(
            $this->appName,
            $this->apiVersion,
            ($defaultShop !== null) ? $defaultShop->getHost() : null
        );
    }

    /**
     * First tries to find currently logged in user in the database and checks their stripe customer id.
     * If found, the customer information is loaded from Stripe and returned.
     *
     * @return StripeCustomer|null
     */
    public function getCurrentCustomer()
    {
        if ($this->cachedStripeCustomer !== null) {
            return $this->cachedStripeCustomer;
        }

        $customer = $this->customerService->getCurrent();

        if ($customer === null ||
            $this->customerService->isDisabled($customer) ||
            $this->customerService->hasNotStripeId($customer)) {
            return null;
        }

        $stripeCustomerId = $customer->getAttribute()->getStripeCustomerId();

        try {
            $stripeCustomer = Customer::retrieve($stripeCustomerId);
            if ($stripeCustomer && isset($stripeCustomer->deleted)) {
                throw new Exception('Customer deleted');
            }
            $this->cachedStripeCustomer = $stripeCustomer;
        } catch (Exception $e) {
            /**
             * Customer cannot be found
             * - remove him from cache
             * - remove stripe customerID from database
             */
            $this->cachedStripeCustomer = null;
            $this->customerService->removeStripeId($customer);
        }
        return $this->cachedStripeCustomer;
    }

    /**
     * @param StripeCustomer $customer
     * @return array
     */
    public function getAllCardsOfCustomer($customer)
    {
        if (isset($customer->deleted)) {
            return [];
        }

        // Get information about all card sources
        $cardSources = array_filter($customer->sources->data, function ($source) {
            return $source->type === 'card';
        });
        $cards = array_map(function ($source) {
            return [
                'id' => $source->id,
                'name' => $source->owner->name,
                'brand' => $source->card->brand,
                'last4' => $source->card->last4,
                'exp_month' => $source->card->exp_month,
                'exp_year' => $source->card->exp_year
            ];
        }, $cardSources);

        // Sort the cards by id (which correspond to the date, the card was created/added)
        usort($cards, function ($cardA, $cardB) {
            return strcmp($cardA['id'], $cardB['id']);
        });

        return $cards;
    }

    /**
     * Creates a new Stripe customer for given shopware customer and saves
     * the respective ID in the customer attributes.
     *
     * @param ShopwareCustomer $customer
     * @return StripeCustomer
     */
    public function createCustomer($customer)
    {
        $this->customerService->ensureHasAttribute($customer);

        $stripeCustomer = StripeCustomer::create(
            [
                'description' => $this->customerService->getName($customer),
                'email' => $customer->getEmail(),
                'metadata' => [
                    'platform_name' => $this->appName
                ]
            ]
        );
        $this->customerService->addStripeId($customer, $stripeCustomer->id);

        return $stripeCustomer;
    }

}
