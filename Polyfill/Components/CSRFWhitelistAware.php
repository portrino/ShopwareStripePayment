<?php
/**
 * This file is a polyfill for the 'Shopware\Components\CSRFWhitelistAware' interface that is available starting from
 * Shopware v5.2.0. Don't include this file directly, but use 'Polyfill/Loader.php' instead.
 */

namespace Shopware\Components;

if (!interface_exists('\Shopware\Components\CSRFWhitelistAware')) {
    // Just define an empty interface
    interface CSRFWhitelistAware
    {
    }
}
