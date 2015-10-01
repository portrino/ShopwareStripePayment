<?php

namespace Shopware\Plugins\ViisonStripePayment\Subscriber;

use Enlight\Event\SubscriberInterface,
	\Shopware_Plugins_Frontend_ViisonStripePayment_Bootstrap as Bootstrap;

/**
 * The subscriber providing the theme/template extensions.
 *
 * @copyright Copyright (c) 2015, VIISON GmbH
 */
class Theme implements SubscriberInterface
{

	private $path;

	private $templateManager;

	/**
	 * @param bootstrap The plugin bootstrap, used as an DI container.
	 */
	public function __construct(Bootstrap $bootstrap) {
		$this->path = $bootstrap->Path();
		$this->templateManager = $bootstrap->get('template');
	}

	/**
	 * @return An array containing all subsciptions.
	 */
	public static function getSubscribedEvents() {
		return array(
			'Enlight_Controller_Action_PostDispatchSecure' => 'onPostDispatchSecure'
		);
	}

	/**
	 * Selectes the template directory based on the requested module as well as the
	 * template version, when requesting the frontend. Backend and API requests
	 * as well as frontend requests with a template version < 3 use the 'old'
	 * emotion templates, whereas frontend requests with a template version >= 3
	 * use the new responsive theme templates.
	 *
	 * @param args The arguments passed by the method triggering the event.
	 */
	public function onPostDispatchSecure(Enlight_Event_EventArgs $args) {
		// Determine template type (responsive/emotion)
		$module = $args->getRequest()->getParam('module');
		$templateType = (!in_array($module, array('backend', 'api')) && Shopware()->Shop()->getTemplate()->getVersion() >= 3) ? 'responsive' : 'emotion';

		// Add the template directory for the used template type
		$this->templateManager->addTemplateDir(
			$this->path . 'Views/' . $templateType . '/',
			'viisonStripePayment'
		);
	}

}
