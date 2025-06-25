# FoF S3 Assets

A [Flarum](http://flarum.org) extension. Relocate Flarum disks onto S3 or compatible bucket

## Installation

Install with composer:

```sh
composer require fof/s3-assets:"*"
```

## Updating

```sh
composer update fof/s3-assets
php flarum cache:clear
```

## Configuration

The S3 (or compatible) bucket can be configured either by environment variables or via the extension settings. If the environment variables are set, they will override the settings entered in the admin panel, if set.

#### Environment variables
- `FOF_S3_ACCESS_KEY_ID` - your access key ID *
- `FOF_S3_SECRET_ACCESS_KEY` - your secret *
- `FOF_S3_REGION` - the region *
- `FOF_S3_BUCKET` - the bucket name *
- `FOF_S3_URL` - the public facing base URL of the bucket
- `FOF_S3_ENDPOINT` - the ARN
- `FOF_S3_ACL` - The ACL, if any, that should be applied to the uploaded object. For possible values, see [AWS Docs](https://docs.aws.amazon.com/AmazonS3/latest/dev/acl-overview.html#canned-acl) 
- `FOF_S3_PATH_STYLE_ENDPOINT` - boolean value
- `FOF_S3_CACHE_CONTROL` - Optional. Specify the `max-age` header files should be served with, for example `3153600` (1 year). `0` or not set = no caching.

`*` denotes the minimum requirements for using S3 on AWS. S3-compatible services will require more.

If you plan to setup the S3 configuration using the environment variables, please ensure these are set _before_ enabling the extension

#### Transferring assets from the existing filesystem to the S3 bucket

After your new bucket is configured, any exisiting files, will not exist there (ie uploaded avatars, profile covers, etc).

Use the provided command to start copying these files. An optional additional paramater `--move` will delete the files from your local filesystem after a successful copy.

```php
php flarum fof:s3:copy --move
```

## Links

- [Discuss](https://discuss.flarum.org/d/PUT_DISCUSS_SLUG_HERE)
