# Elephox Framework

This is a class library providing independent building blocks.

[![Common Workflow](https://github.com/elephox-dev/framework/actions/workflows/common.yml/badge.svg)](https://github.com/elephox-dev/framework/actions/workflows/common.yml)
[📊 Coverage Report](https://elephox.dev/coverage)

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
