<?php
/**
 * This file loads all Shopware polyfills, by registering this path with the Enlight class loader for the 'Shopware'
 * namespace prefix. This achieves backwards compatibility of classes, interfaces etc. that are required in newer
 * Shopware versions (e.g. the CSRFWhitelistAware interface), but not available in older versions.
 */

Shopware()->Container()->get('loader')->registerNamespace('Shopware', (__DIR__ . '/'));
