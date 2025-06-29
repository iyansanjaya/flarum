<p align="center">
<a href="https://packagist.org/packages/flarum/core"><img src="https://poser.pugx.org/flarum/core/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/flarum/core"><img src="https://poser.pugx.org/flarum/core/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/flarum/core"><img src="https://poser.pugx.org/flarum/core/license.svg" alt="License"></a>
</p>

## About Flarum

**[Flarum](https://flarum.org/) is a delightfully simple discussion platform for your website.** It's fast and easy to use, with all the features you need to run a successful community.

## Important Things

After deploying on Zeabur, run this command in Zeabur Dashboard:

```bash
composer update --prefer-dist --no-dev -a
```

And run these command after delete cache, clear cache, re-deploy or disable extension.

**Clear Cache**:

```bash
php flarum cache:clear
```

**Publish Assets**:

```bash
php flarum assets:publish
```

**Run Scheduler**:

```bash
php flarum schedule:run >> /dev/null 2>&1
```

## License

Flarum is open-source software licensed under the [MIT License](https://github.com/flarum/flarum/blob/master/LICENSE).
