<p align="center">
<a href="https://packagist.org/packages/flarum/core"><img src="https://poser.pugx.org/flarum/core/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/flarum/core"><img src="https://poser.pugx.org/flarum/core/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/flarum/core"><img src="https://poser.pugx.org/flarum/core/license.svg" alt="License"></a>
</p>

## About Flarum

**[Flarum](https://flarum.org/) is a delightfully simple discussion platform for your website.** It's fast and easy to use, with all the features you need to run a successful community. It is designed to be:

- **Fast and simple.** No clutter, no bloat, no complex dependencies. Flarum is built with PHP so it’s quick and easy to deploy. The interface is powered by Mithril, a performant JavaScript framework with a tiny footprint.

- **Beautiful and responsive.** This is forum software for humans. Flarum is carefully designed to be consistent and intuitive across platforms, out-of-the-box.

- **Powerful and extensible.** Customize, extend, and integrate Flarum to suit your community. Flarum’s architecture is amazingly flexible, with a powerful Extension API.

## Important Things

After deploying at Zeabur, run this command in Zeabur Dashboard:

`composer update --prefer-dist --no-dev -a`

And run these command after delete cache, clear cache, re-deploy or disable extension.

**Clear Cache**: `php flarum cache:clear`

**Publish Assets**: `php flarum assets:publish`

###

## Installation

Read the **[Installation guide](https://docs.flarum.org/install)** to get started. For support, refer to the [documentation](https://docs.flarum.org/), and ask questions on the [community forum](https://discuss.flarum.org/) or [Discord chat](https://flarum.org/discord/).

## Contributing

Thank you for considering contributing to Flarum! Please read the **[Contributing guide](https://docs.flarum.org/contributing)** to learn how you can help.

This repository only holds the Flarum skeleton application. Most development happens in [flarum/core](https://github.com/flarum/core).

## Security Vulnerabilities

If you discover a security vulnerability within Flarum, please follow our [security policy](https://github.com/flarum/core/security/policy) so we can address it promptly.

## License

Flarum is open-source software licensed under the [MIT License](https://github.com/flarum/flarum/blob/master/LICENSE).
