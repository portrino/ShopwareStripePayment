<?php
// Copyright (c) Pickware GmbH. All rights reserved.
// This file is part of software that is released under a proprietary license.
// You must not copy, modify, distribute, make publicly available, or execute
// its contents or parts thereof without express permission by the copyright
// holder, unless otherwise permitted by law.

/**
 * This file loads all Shopware polyfills, by registering this path with the Enlight class loader for the 'Shopware'
 * namespace prefix. This achieves backwards compatibility of classes, interfaces etc. that are required in newer
 * Shopware versions (e.g. the CSRFWhitelistAware interface), but not available in older versions.
 */

Shopware()->Container()->get('loader')->registerNamespace('Shopware', (__DIR__ . '/'));
