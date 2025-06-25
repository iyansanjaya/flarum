<?php

/*
 * This file is part of fof/s3-assets.
 *
 * Copyright (c) FriendsOfFlarum
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\S3Assets\Driver;

use Flarum\Filesystem\DriverInterface;
use Flarum\Foundation\Config;
use Flarum\Foundation\Paths;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\S3Assets\Driver\Config as DriverConfig;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;

class S3Driver implements DriverInterface
{
    protected FilesystemManager $manager;

    public function __construct(
        protected Paths $paths,
        protected DriverConfig $config,
        Container $container
    ) {
        $this->manager = new FilesystemManager($container);
    }

    public function build(
        string $diskName,
        SettingsRepositoryInterface $settings,
        Config $config,
        array $localConfig
    ): Cloud {
        if (empty($this->config->config())) {
            // @phpstan-ignore-next-line
            return $this->manager->createLocalDriver($localConfig);
        }

        $root = Arr::get($localConfig, 'root');
        $root = str_replace($this->paths->public, '', $root);

        $driver = $this->manager->createS3Driver(array_merge(
            $this->config->config(),
            ['root' => $root]
        ));

        return $driver;
    }
}
