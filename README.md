# philly-framework/base

This is a class library providing independent building blocks.

[![Common Workflow](https://github.com/philly-framework/base/actions/workflows/common.yml/badge.svg)](https://github.com/philly-framework/base/actions/workflows/common.yml)
[📊 Coverage Report](https://philly.ricardoboss.de/coverage)

## Goals:

- [x] Dependency Injection
  - [x] Container
  - [x] Dynamic lifespan (request/transient)
- [x] composer.json dependency sync across all modules (achieved using [wikimedia/composer-merge-plugin](https://github.com/wikimedia/composer-merge-plugin))
- [x] Http Client
- [ ] Database Adapter
  - [ ] MySql Implementation
  - [ ] Entity Mapping
- [ ] Routing (controller attributes)
- [ ] Templating Adapter
  - [ ] Twig Implementation
