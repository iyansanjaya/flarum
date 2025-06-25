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

use Flarum\Settings\SettingsRepositoryInterface;
use FoF\S3Assets\Validator\S3DiskConfigValidator;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Psr\Log\LoggerInterface;

class Config
{
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected S3DiskConfigValidator $validator,
        protected LoggerInterface $logger,
    ) {
    }

    public function shouldUseEnv(): bool
    {
        return env('FOF_S3_ACCESS_KEY_ID') && env('FOF_S3_SECRET_ACCESS_KEY') && env('FOF_S3_BUCKET') && env('FOF_S3_REGION');
    }

    public function config(): array
    {
        // Determine whether to use environment variables or settings
        $useEnv = $this->shouldUseEnv();

        // Build configuration
        $config = $useEnv
            ? $this->buildConfigFromEnv()
            : $this->buildConfigFromSettings();

        // Validate configuration
        try {
            $this->validator->assertValid($config);
        } catch (IlluminateValidationException $e) {
            $this->logger->error('[fof-s3-assets] Invalid S3 disk configuration', ['errors' => $e->errors()]);

            return [];
        }

        return $config;
    }

    protected function buildConfigArray(string $key, string $secret, string $region, string $bucket, string $cdnUrl, ?string $endpoint, ?bool $pathStyle, ?string $acl, ?int $cache = null, bool $setByEnv = false, string $driver = 's3'): array
    {
        // These are the required values for AWS S3.
        // Some S3-compatible services may require additional values, so we check if any of these are set below.
        $config = [
            'driver'                  => $driver,
            'key'                     => $key,
            'secret'                  => $secret,
            'region'                  => $region,
            'bucket'                  => $bucket,
            'url'                     => $cdnUrl,
            'set_by_environment'      => $setByEnv,
            'options'                 => [],
        ];

        // These values are generally only required for S3-compatible services.

        if ($endpoint) {
            $config['endpoint'] = $endpoint;
        }

        if ($pathStyle) {
            $config['use_path_style_endpoint'] = $pathStyle;
        }

        if ($acl) {
            $config['options']['ACL'] = $acl;
        }

        if ($cache) {
            $config['options']['CacheControl'] = "max-age=$cache";
        }

        return $config;
    }

    protected function buildConfigFromEnv(): array
    {
        $bucket = env('FOF_S3_BUCKET');
        $region = env('FOF_S3_REGION');
        $cdnUrl = env('FOF_S3_URL', $this->createAwsUrlFromBucketAndRegion($bucket, $region));
        $endpoint = env('FOF_S3_ENDPOINT');
        $pathStyle = (bool) env('FOF_S3_PATH_STYLE_ENDPOINT', false);
        $acl = env('FOF_S3_ACL');
        $cache = env('FOF_S3_CACHE_CONTROL');

        return $this->buildConfigArray(
            key: env('FOF_S3_ACCESS_KEY_ID'),
            secret: env('FOF_S3_SECRET_ACCESS_KEY'),
            region: $region,
            bucket: $bucket,
            cdnUrl: $cdnUrl,
            endpoint: $endpoint,
            pathStyle: $pathStyle,
            acl: $acl,
            cache: $cache,
            setByEnv: true
        );
    }

    protected function buildConfigFromSettings(): array
    {
        $bucket = $this->getSetting('awsS3Bucket', '');
        $region = $this->getSetting('awsS3Region', '');
        $cdnUrl = $this->getSetting('cdnUrl', $this->createAwsUrlFromBucketAndRegion($bucket, $region));
        $endpoint = $this->getSetting('awsS3Endpoint');
        $pathStyle = (bool) $this->getSetting('awsS3UsePathStyleEndpoint', false);
        $acl = $this->getSetting('awsS3ACL');
        $cache = $this->getSetting('awsS3CacheControl');

        return $this->buildConfigArray(
            key: $this->getSetting('awsS3Key', ''),
            secret: $this->getSetting('awsS3Secret', ''),
            region: $region,
            bucket: $bucket,
            cdnUrl: $cdnUrl,
            endpoint: $endpoint,
            pathStyle: $pathStyle,
            acl: $acl,
            cache: $cache,
            setByEnv: false
        );
    }

    protected function getSetting(string $key, mixed $default = null): mixed
    {
        $prefix = $this->getSettingsPrefix();
        $value = $this->settings->get("$prefix.$key");

        return empty($value) ? $default : $value;
    }

    protected function createAwsUrlFromBucketAndRegion(?string $bucket, ?string $region): string
    {
        if (!$bucket || !$region) {
            return '';
        }

        return sprintf('https://%s.s3.%s.amazonaws.com', $bucket, $region);
    }

    protected function getSettingsPrefix(): string
    {
        $shareWithFoFUpload = (bool) $this->settings->get('fof-s3-assets.share_s3_config_with_fof_upload');

        if ($shareWithFoFUpload) {
            return 'fof-upload';
        }

        return 'fof-s3-assets';
    }
}
