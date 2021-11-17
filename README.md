# Elephox Framework

This is a class library providing independent building blocks.

[![Latest Stable Version](http://poser.pugx.org/elephox/framework/v)](https://packagist.org/packages/elephox/framework)
[![License](http://poser.pugx.org/elephox/framework/license)](https://packagist.org/packages/elephox/framework)
[![PHP Version Require](http://poser.pugx.org/elephox/framework/require/php)](https://packagist.org/packages/elephox/framework)
[![Psalm Level](https://shepherd.dev/github/elephox-dev/framework/level.svg)](https://shepherd.dev/github/elephox-dev/framework)
[![Type Coverage](https://shepherd.dev/github/elephox-dev/framework/coverage.svg)](https://shepherd.dev/github/elephox-dev/framework)
[![Coverage Status](https://coveralls.io/repos/github/elephox-dev/framework/badge.svg?branch=main)](https://coveralls.io/github/elephox-dev/framework?branch=main)
[![Common Workflow](https://github.com/elephox-dev/framework/actions/workflows/common.yml/badge.svg)](https://github.com/elephox-dev/framework/actions/workflows/common.yml)

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
  - [ ] find a better way to choose the correct binding
- [ ] Core/src/Handler/Handlers.php
  - [ ] find a better way to load the App\ namespace
- [ ] Database/src/AbstractRepository.php
  - [ ] Implement first() method.
  - [ ] Implement any() method.
  - [ ] Implement where() method.
  - [ ] Implement contains() method.
  - [ ] Implement find() method.
  - [ ] Implement findAll() method.
  - [ ] Implement add() method.
  - [ ] Implement update() method.
  - [ ] Implement delete() method.

<!-- end todos -->
