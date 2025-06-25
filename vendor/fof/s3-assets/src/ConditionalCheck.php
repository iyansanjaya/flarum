<?php

/*
 * This file is part of fof/s3-assets.
 *
 * Copyright (c) FriendsOfFlarum
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\S3Assets;

use Exception;
use FoF\S3Assets\Driver\Config as DriverConfig;
use FoF\S3Assets\Validator\S3DiskConfigValidator;
use Illuminate\Contracts\Cache\Store;

class ConditionalCheck
{
    const CACHE_KEY = 'fof-s3-assets-config-valid';

    public function __construct(
        protected DriverConfig $config,
        protected S3DiskConfigValidator $validator,
        protected Store $cache
    ) {
    }

    /**
     * Checks to see if the supplied config passes the required checks.
     */
    public function validConfig(): bool
    {
        // If we have a cached value, return it
        if (($cacheValue = $this->cache->get(self::CACHE_KEY)) && $cacheValue !== null) {
            return $cacheValue;
        }

        // If we don't have a cached value, check the config
        $config = $this->config->config();

        if (empty($config)) {
            return false;
        }

        try {
            $this->validator->assertValid($config);
            $this->cache->forever(self::CACHE_KEY, true);
        } catch (Exception $e) {
            $this->cache->forever(self::CACHE_KEY, false);

            return false;
        }

        return true;
    }
}
