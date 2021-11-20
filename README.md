# Elephox Framework

This is a class library providing independent building blocks.

[![Latest Stable Version](http://poser.pugx.org/elephox/framework/v)](https://packagist.org/packages/elephox/framework)
[![License](http://poser.pugx.org/elephox/framework/license)](https://packagist.org/packages/elephox/framework)
[![PHP Version Require](http://poser.pugx.org/elephox/framework/require/php)](https://packagist.org/packages/elephox/framework)

[![Psalm Level](https://shepherd.dev/github/elephox-dev/framework/level.svg)](https://shepherd.dev/github/elephox-dev/framework)
[![Type Coverage](https://shepherd.dev/github/elephox-dev/framework/coverage.svg)](https://shepherd.dev/github/elephox-dev/framework)
[![Coverage Status](https://coveralls.io/repos/github/elephox-dev/framework/badge.svg?branch=main)](https://coveralls.io/github/elephox-dev/framework?branch=main)

[![Linux 🐧](https://github.com/elephox-dev/framework/actions/workflows/linux.yml/badge.svg)](https://github.com/elephox-dev/framework/actions/workflows/linux.yml)
[![Windows 🪟](https://github.com/elephox-dev/framework/actions/workflows/windows.yml/badge.svg)](https://github.com/elephox-dev/framework/actions/workflows/windows.yml)

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
- [ ] Core/src/Handler/Attribute/RequestHandler.php
  - [ ] extract url parameters and pass them inside the arguments
- [ ] Core/src/Handler/HandlerContainer.php
  - [ ] find a better way to choose the correct binding if there are multiple applicable bindings


## Open issues from other Repositories

- [vimeo/psalm](https://github.com/vimeo/psalm)
  - [#6821](https://github.com/vimeo/psalm/issues/6821)
  - [#6468](https://github.com/vimeo/psalm/issues/6468)
  - [#6429](https://github.com/vimeo/psalm/issues/6429)

<!-- end todos -->
