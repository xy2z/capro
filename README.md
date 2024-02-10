<div align="center">

![capro logo](./capro-logo.png)

Capro is a static-site generator for PHP 8+

---

[Quick Setup](#quick-setup) | [Documentation](https://capro.xy2z.io) | [Community](https://github.com/xy2z/capro/discussions) | [Sponsor](https://github.com/sponsors/xy2z)

<p align="center">
  <a href="https://github.com/xy2z/capro/releases"><img src="https://img.shields.io/github/v/release/xy2z/capro?style=flat-square&include_prereleases&sort=semver"></a>
  <a href="https://github.com/xy2z/capro/blob/master/LICENSE"><img src="https://img.shields.io/github/license/xy2z/capro?style=flat-square&color=blue"></a>
  <a href="https://github.com/xy2z/capro/graphs/contributors"><img src="https://img.shields.io/github/contributors/xy2z/capro?style=flat-square"></a>
  <a href="https://github.com/xy2z/capro/issues?q=is%3Aopen+is%3Aissue+label%3A%22help+wanted%22"><img src="https://img.shields.io/github/issues/xy2z/capro/help%20wanted?label=help%20wanted%20issues&style=flat-square&color=f26222"></a>
  <a href="https://github.com/xy2z/capro/milestone/1"><img src="https://img.shields.io/github/milestones/progress-percent/xy2z/capro/1?label=alpha%20completion&style=flat-square"></a>
</p>

---

</div>


## ⚠ Notice

Capro is currently in [alpha](https://github.com/xy2z/capro/issues/5), please report bugs via [GitHub Issues](https://github.com/xy2z/capro/issues)


## About

A PHP static-site generator, using Blade template engine.

Capro is currently in alpha, and is not thoroughly tested for production. Breaking changes might occur during the alpha phase.


## Sponsors

Currently none. Sponsor here: https://github.com/sponsors/xy2z


## Requirements
- PHP 8.0+
- [Composer](https://getcomposer.org/)


## Quick Setup
First install the capro global package
```bash
composer global require xy2z/capro:@alpha
```

Now, you can create new sites using:
```bash
capro new demo
cd demo/public
php -S localhost:82
```
Head over to http://localhost:82 to see your new static Capro site.

Learn more in the **[full documentation site](https://capro.xy2z.io).** (which was build in capro)


## Contributing

Pull Requests are welcome! See more in the [Contributing Guide](CONTRIBUTING.md).
