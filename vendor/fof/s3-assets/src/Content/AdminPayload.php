<?php

/*
 * This file is part of fof/s3-assets.
 *
 * Copyright (c) FriendsOfFlarum
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\S3Assets\Content;

use Flarum\Foundation\Config;
use Flarum\Frontend\Document;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\S3Assets\Driver\Config as S3Config;
use FoF\S3Assets\Repository\S3Repository;

class AdminPayload
{
    public function __construct(
        protected Config $config,
        protected SettingsRepositoryInterface $settings,
        protected S3Repository $s3,
        protected S3Config $s3Config
    ) {
    }

    public function __invoke(Document $document)
    {
        $document->payload['s3SetByEnv'] = $this->s3Config->shouldUseEnv();
        $document->payload['FoFS3ShareWithFoFUpload'] = $this->settings->get('fof-s3-assets.share_s3_config_with_fof_upload');
        $document->payload['cdn'] = $this->s3->cdnHost();
    }
}
