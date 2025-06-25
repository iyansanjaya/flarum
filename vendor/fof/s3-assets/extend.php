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

use Flarum\Extend;
use Flarum\Foundation\Event\ClearingCache;
use Flarum\Settings\Event\Saving as SettingsSaving;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less')
        ->content(Content\AdminPayload::class),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Settings())
        ->default('fof-s3-assets.share_s3_config_with_fof_upload', false),

    (new Extend\ServiceProvider())
        ->register(Provider\S3DiskProvider::class),

    (new Extend\Event())
        ->listen(SettingsSaving::class, Listener\SettingsChanged::class),

    new S3Lifecycle(),

    (new Extend\Conditional())
        ->when(resolve(ConditionalCheck::class)->validConfig(), fn () => [
            (new Extend\Console())
                ->command(Console\CopyAssetsCommand::class),

            (new Extend\Filesystem())
                ->driver('s3', Driver\S3Driver::class)
                ->driver('local', Driver\S3Driver::class),

            (new Extend\Event())
                ->listen(ClearingCache::class, Listener\RepublishAssets::class),
        ]),
];
