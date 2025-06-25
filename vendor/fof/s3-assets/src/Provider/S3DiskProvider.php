<?php

/*
 * This file is part of fof/s3-assets.
 *
 * Copyright (c) FriendsOfFlarum
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\S3Assets\Provider;

use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Frontend\Compiler\VersionerInterface;
use FoF\S3Assets\Driver\Config;
use FoF\S3Assets\Frontend\Versioner;

class S3DiskProvider extends AbstractServiceProvider
{
    public static bool $bindVersioner = true;

    public function register()
    {
        if (static::$bindVersioner) {
            $this->container->bind(VersionerInterface::class, Versioner::class);
        }

        $this->container->singleton(Config::class);
    }
}
