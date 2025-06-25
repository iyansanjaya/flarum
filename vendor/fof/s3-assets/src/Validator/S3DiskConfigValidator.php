<?php

/*
 * This file is part of fof/s3-assets.
 *
 * Copyright (c) FriendsOfFlarum
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\S3Assets\Validator;

use Flarum\Foundation\AbstractValidator;

class S3DiskConfigValidator extends AbstractValidator
{
    protected $rules = [
        'driver'                  => ['required', 'string', 'in:s3'],
        'key'                     => ['required', 'string'],
        'secret'                  => ['required', 'string'],
        'region'                  => ['required', 'string'],
        'bucket'                  => ['required', 'string'],
        'url'                     => ['url'],
        'endpoint'                => ['url'],
        'use_path_style_endpoint' => ['required', 'bool'],
        'options.ACL'             => ['string'],
        'options.CacheControl'    => ['string'],
        'set_by_environment'      => ['required', 'bool'],
    ];
}
