<?php
// Copyright (c) Pickware GmbH. All rights reserved.
// This file is part of software that is released under a proprietary license.
// You must not copy, modify, distribute, make publicly available, or execute
// its contents or parts thereof without express permission by the copyright
// holder, unless otherwise permitted by law.

/**
 * This file is a polyfill for the 'Shopware\Components\CSRFWhitelistAware' interface that is available starting from
 * Shopware v5.2.0. Don't include this file directly, but use 'Polyfill/Loader.php' instead.
 */

namespace Shopware\Components;

if (!interface_exists('Shopware\\Components\\CSRFWhitelistAware')) {
    // Just define an empty interface
    interface CSRFWhitelistAware
    {
    }
}
