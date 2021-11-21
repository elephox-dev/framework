# Elephox Framework

This is a class library providing independent building blocks.

<p style="text-align: center">
  <a href="https://packagist.org/packages/elephox/framework"><img src="http://poser.pugx.org/elephox/framework/v" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/elephox/framework"><img src="http://poser.pugx.org/elephox/framework/license" alt="License"></a>
  <a href="https://packagist.org/packages/elephox/framework"><img src="http://poser.pugx.org/elephox/framework/require/php" alt="PHP Version Require"></a>
  <a href="https://shepherd.dev/github/elephox-dev/framework"><img src="https://shepherd.dev/github/elephox-dev/framework/level.svg" alt="Psalm Level"></a>
  <a href="https://shepherd.dev/github/elephox-dev/framework"><img src="https://shepherd.dev/github/elephox-dev/framework/coverage.svg" alt="Type Coverage"></a>
  <a href="https://coveralls.io/github/elephox-dev/framework?branch=main"><img src="https://coveralls.io/repos/github/elephox-dev/framework/badge.svg?branch=main" alt="Coverage Status"></a>
  <a href="https://github.com/elephox-dev/framework/actions/workflows/linux.yml"><img src="https://github.com/elephox-dev/framework/actions/workflows/linux.yml/badge.svg" alt="Linux 🐧"></a>
  <a href="https://github.com/elephox-dev/framework/actions/workflows/windows.yml"><img src="https://github.com/elephox-dev/framework/actions/workflows/windows.yml/badge.svg" alt="Windows 🪟"></a>
</p>

## Goals:

- [x] Dependency Injection
  - [x] Container
  - [x] Dynamic lifespan (request/transient)
- [x] composer.json dependency sync across all modules (achieved using [wikimedia/composer-merge-plugin](https://github.com/wikimedia/composer-merge-plugin))
- [x] Http Client
- [x] Optimize common workflow (re-use coverage data)
- [ ] Filesystem
- [ ] Database Adapter
  - [ ] MySql Implementation
  - [ ] Entity Mapping
- [x] Routing (controller attributes)
- [ ] Templating Adapter
  - [ ] Twig Implementation

<!-- start todos -->

## TODOs Found:

### TODO

- [ ] Core/src/Handler/Attribute/ExceptionHandler.php
  - [ ] do something else with this
- [ ] Core/src/Handler/HandlerContainer.php
  - [ ] find a better way to choose the correct binding if there are multiple applicable bindings


## Open issues from other Repositories

- [vimeo/psalm](https://github.com/vimeo/psalm)
  - [#6821](https://github.com/vimeo/psalm/issues/6821)
  - [#6468](https://github.com/vimeo/psalm/issues/6468)
  - [#6429](https://github.com/vimeo/psalm/issues/6429)

<!-- end todos -->
