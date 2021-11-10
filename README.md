# philly-framework/base

This is a class library providing independent building blocks.

[![🔎 Static Analysis](https://github.com/philly-framework/base/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/philly-framework/base/actions/workflows/static-analysis.yml)
[![🧟 Mutation Tests](https://github.com/philly-framework/base/actions/workflows/mutation-tests.yml/badge.svg)](https://github.com/philly-framework/base/actions/workflows/mutation-tests.yml)
[![🧪 Unit Tests](https://github.com/philly-framework/base/actions/workflows/unit-tests.yml/badge.svg)](https://github.com/philly-framework/base/actions/workflows/unit-tests.yml)
[![📊 Coverage](https://github.com/philly-framework/base/actions/workflows/coverage.yml/badge.svg)](https://philly.ricardoboss.de/coverage/)

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
