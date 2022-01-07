<p align=center>
  <img src="https://raw.githubusercontent.com/elephox-dev/.github/main/profile/logo.svg" alt="Elephox Logo" height=100>
</p>

<p align=center>
  This is a library project providing building blocks for building your own PHP application.
</p>

<p align="center">
  <a href="https://packagist.org/packages/elephox/framework"><img src="https://poser.pugx.org/elephox/framework/v" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/elephox/framework"><img src="https://poser.pugx.org/elephox/framework/license" alt="License"></a>
  <a href="https://packagist.org/packages/elephox/framework"><img src="https://poser.pugx.org/elephox/framework/require/php" alt="PHP Version Require"></a>
  <a href="https://shepherd.dev/github/elephox-dev/framework"><img src="https://shepherd.dev/github/elephox-dev/framework/level.svg" alt="Psalm Level"></a>
  <a href="https://shepherd.dev/github/elephox-dev/framework"><img src="https://shepherd.dev/github/elephox-dev/framework/coverage.svg" alt="Type Coverage"></a>
  <a href="https://coveralls.io/github/elephox-dev/framework?branch=main"><img src="https://coveralls.io/repos/github/elephox-dev/framework/badge.svg?branch=main" alt="Coverage Status"></a>
  <a href="https://github.com/elephox-dev/framework/actions/workflows/ci.yml"><img src="https://github.com/elephox-dev/framework/actions/workflows/ci.yml/badge.svg" alt="CI"></a>
</p>

## 📚 Documentation

Visit [elephox.dev](https://elephox.dev) for the documentation.

## 🎯 Goals

### 🔳 Open

- [ ] Implement [PSR-14](https://www.php-fig.org/psr/psr-14) adapter for Event Bus
- [ ] Integrate PIE into Collections
- [ ] Caching Services
- [ ] Implement [PSR-6](https://www.php-fig.org/psr/psr-6) adapter for Caching Services
- [ ] Implement [PSR-16](https://www.php-fig.org/psr/psr-16) adapter for Caching Services
- [ ] Implement [PSR-3](https://www.php-fig.org/psr/psr-3) adapter for Logging Services
- [ ] Database Adapter
- [ ] MySql Adapter Implementation
- [ ] Entity Mapping for Database Adapter
- [ ] Templating Adapter
- [ ] Twig Adapter Implementation
- [ ] Publish PHPUnit HTML coverage report
- [ ] Publish/compare benchmark report against baseline
- [ ] Create async application servers, like [laravel/octane](https://github.com/laravel/octane)
- [ ] Provide an easier way to create a development environment (Docker, Vagrant, NixOS?)
- [ ] New documentation solution
- [ ] Improve integration of PHPStorm attributes
- [ ] Implement [PSR-7](https://www.php-fig.org/psr/psr-7) adapter for HTTP
- [ ] Implement [PSR-17](https://www.php-fig.org/psr/psr-17) adapter for HTTP
- [ ] Implement [PSR-15](https://www.php-fig.org/psr/psr-15) adapter for Core
- [ ] Add a formatter (PHP CS Fixer)
- [ ] Add [phpspy](https://github.com/adsr/phpspy) and flame graphs 🔥
- [ ] Finish PIE implementation (grouping)
- [ ] Add READMEs, LICENSEs and PHPUnit configs for all modules
- [ ] Maybe: Query Builder for Database Adapter
- [ ] Maybe: Implement [PSR-13](https://www.php-fig.org/psr/psr-13) adapter for Templating Adapter
- [ ] Maybe: Implement and provide [PSR-20](https://github.com/php-fig/fig-standards/blob/master/proposed/clock.md) adapter

### ☑️ Done

- [x] ~~Dependency Injection~~
- [x] ~~DI Container~~
- [x] ~~DI Dynamic object lifespan (request/transient)~~
- [x] ~~Implement [PSR-11](https://www.php-fig.org/psr/psr-11) in DI~~
- [x] ~~composer.json dependency sync (see [elephox-dev/composer-module-sync](https://github.com/elephox-dev/composer-module-sync))~~
- [x] ~~Http Messages~~
- [x] ~~Optimize common workflow (re-use coverage data)~~
- [x] ~~(basic) Filesystem~~
- [x] ~~Routing (controller attributes)~~
- [x] ~~Logging Services~~
- [x] ~~Create a makefile with useful shortcuts~~ (Created composer.json scripts instead)
- [x] ~~Event Bus~~
- [x] ~~Split existing PSR implementations into different third-party adapters~~

<!-- start annotations -->

## 📋 Source code annotations

### ✅ TODO

- [ ] [PIE/src/IsEnumerable.php](https://github.com/elephox-dev/framework/tree/main/modules/PIE/src/IsEnumerable.php)
  - [ ] rewrite some functions to use aggregate
  - [ ] Implement groupBy() method.
  - [ ] Implement groupJoin() method.

### 🤔 MAYBE

- [ ] [PIE/src/IsEnumerable.php](https://github.com/elephox-dev/framework/tree/main/modules/PIE/src/IsEnumerable.php)
  - [ ] rewrite this using AppendIterator and LimitIterator


### 🚧 Open issues from other repositories

- [phpspec/prophecy](https://github.com/phpspec/prophecy)
  - [#550](https://github.com/phpspec/prophecy/issues/550)

- [actions/upload-artifact](https://github.com/actions/upload-artifact)
  - [#270](https://github.com/actions/upload-artifact/issues/270)

- [vimeo/psalm](https://github.com/vimeo/psalm)
  - [#6821](https://github.com/vimeo/psalm/issues/6821)
  - [#7322](https://github.com/vimeo/psalm/issues/7322)

<!-- end annotations -->
