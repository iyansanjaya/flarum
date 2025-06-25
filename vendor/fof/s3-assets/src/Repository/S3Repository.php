<?php

/*
 * This file is part of fof/s3-assets.
 *
 * Copyright (c) FriendsOfFlarum
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\S3Assets\Repository;

use Flarum\Foundation\Console\AssetsPublishCommand;
use Flarum\Foundation\Console\CacheClearCommand;
use FoF\S3Assets\Driver\Config as DriverConfig;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class S3Repository
{
    public function __construct(
        protected LoggerInterface $logger,
        protected DriverConfig $config,
        protected CacheClearCommand $cache,
        protected AssetsPublishCommand $publish,
    ) {
    }

    public function cdnHost(): ?string
    {
        return Arr::get($this->config->config(), 'url');
    }

    /**
     * Just a helper method to call the Flarum clear cache command.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->cache->run(
            new ArrayInput([]),
            new NullOutput()
        );
    }

    /**
     * Just a helper method to call the Flarum publish assets command.
     *
     * @return void
     */
    public function publishAssets(): void
    {
        $this->publish->run(
            new ArrayInput([]),
            new NullOutput()
        );
    }
}
