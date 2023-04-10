<p align=center>
  <img src="https://raw.githubusercontent.com/elephox-dev/.github/main/profile/logo.svg" alt="Elephox Logo" height=100>
</p>

<p align=center>
  This is a library project providing building blocks for building your own PHP application.
</p>

<p align="center">
  <a href="https://github.com/elephox-dev/framework/actions/workflows/ci.yml"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/elephox-dev/framework/ci.yml?branch=develop&label=CI&logo=github&style=for-the-badge"></a>
  <a href="https://packagist.org/packages/elephox/framework"><img src="https://img.shields.io/packagist/l/elephox/framework?style=for-the-badge" alt="License"></a>
  <a href="https://packagist.org/packages/elephox/framework"><img src="https://img.shields.io/packagist/v/elephox/framework?label=version&style=for-the-badge" alt="Current Version"></a>
  <a href="https://packagist.org/packages/elephox/framework"><img src="https://img.shields.io/packagist/php-v/elephox/framework?style=for-the-badge&logo=php" alt="PHP Version Require"></a>
  <br>
  <a href="https://shepherd.dev/github/elephox-dev/framework"><img src="https://img.shields.io/endpoint?url=https://shepherd.dev/github/elephox-dev/framework/level&style=for-the-badge" alt="Psalm Level"></a>
  <a href="https://shepherd.dev/github/elephox-dev/framework"><img src="https://img.shields.io/endpoint?url=https://shepherd.dev/github/elephox-dev/framework/coverage&style=for-the-badge&label=type%20coverage" alt="Type Coverage"></a>
  <a href="https://coveralls.io/github/elephox-dev/framework?branch=develop"><img src="https://img.shields.io/coveralls/github/elephox-dev/framework/develop?style=for-the-badge&label=test%20coverage" alt="Coverage Status"></a>
  <a href="https://dashboard.stryker-mutator.io/reports/github.com/elephox-dev/framework/develop"><img src="https://img.shields.io/endpoint?style=for-the-badge&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Felephox-dev%2Fframework%2Fdevelop" alt="Mutation Score Indicator"></a>
</p>

## 📚 Documentation

Visit [elephox.dev](https://elephox.dev) for the documentation.

## 🎯 Goals

Take a look at the goals for the first stable release here: [Milestone 1](https://github.com/elephox-dev/framework/milestone/1)

The overall project goals and future planning is done in the [GitHub projects for this repository](https://github.com/elephox-dev/framework/projects).

## ✨ Contributing

Contributions in all forms are welcome. Make sure to read [elephox.dev/contributing](https://elephox.dev/contributing) for the details.

## 🏷️ Get the Badge

Using Elephox in your project? Add a badge to your README:

[![Elephox Framework](https://img.shields.io/badge/framework-Elephox-blue?style=flat)](https://elephox.dev)

Replace the value of the `style` parameter for different styles (`flat`, `flat-square`, `plastic`, `for-the-badge`).

```markdown
[![Elephox Framework](https://img.shields.io/badge/framework-Elephox-blue?style=flat)](https://elephox.dev)
```
```html
<a href="https://elephox.dev"><img alt="Elephox Framework" src="https://img.shields.io/badge/framework-Elephox-blue?style=flat"></a>
```

<!-- start annotations -->

## 📋 Source code annotations

### ✅ To Do

- [ ] [modules/Collection/src/ArrayList.php](https://github.com/elephox-dev/framework/tree/develop/modules/Collection/src/ArrayList.php)
  - [ ] replace generic enumerable function with array-specific functions where possible
- [ ] [modules/Collection/src/ArrayMap.php](https://github.com/elephox-dev/framework/tree/develop/modules/Collection/src/ArrayMap.php)
  - [ ] replace generic enumerable function with array-specific functions where possible
- [ ] [modules/Collection/src/ArraySet.php](https://github.com/elephox-dev/framework/tree/develop/modules/Collection/src/ArraySet.php)
  - [ ] replace generic enumerable function with array-specific functions where possible
- [ ] [modules/Collection/src/IsKeyedEnumerable.php](https://github.com/elephox-dev/framework/tree/develop/modules/Collection/src/IsKeyedEnumerable.php)
  - [ ] rewrite more functions to use iterators
- [ ] [modules/Collection/src/Iterator/OrderedIterator.php](https://github.com/elephox-dev/framework/tree/develop/modules/Collection/src/Iterator/OrderedIterator.php)
  - [ ] cache keys so they won't have to be re-calculated
- [ ] [modules/Collection/src/ObjectSet.php](https://github.com/elephox-dev/framework/tree/develop/modules/Collection/src/ObjectSet.php)
  - [ ] use this style of assertion error messages for all assertions
- [ ] [modules/DI/src/ServiceDescriptor.php](https://github.com/elephox-dev/framework/tree/develop/modules/DI/src/ServiceDescriptor.php)
  - [ ] Update TImplementation to extend TService once vimeo/psalm#7795 is resolved.
- [ ] [modules/Files/test/DirectoryTest.php](https://github.com/elephox-dev/framework/tree/develop/modules/Files/test/DirectoryTest.php)
  - [ ] add test for symlink
- [ ] [modules/Http/test/GeneratesResponsesTest.php](https://github.com/elephox-dev/framework/tree/develop/modules/Http/test/GeneratesResponsesTest.php)
  - [ ] Add test for both cases in which mime_content_type exists and not
- [ ] [modules/Logging/test/SimpleFormatColorSinkTest.php](https://github.com/elephox-dev/framework/tree/develop/modules/Logging/test/SimpleFormatColorSinkTest.php)
  - [ ] write tests for background and options
- [ ] [modules/Web/src/Routing/InvalidRequestController.php](https://github.com/elephox-dev/framework/tree/develop/modules/Web/src/Routing/InvalidRequestController.php)
  - [ ] change message to be more general and/or create more specific exceptions

### ⚠️ Fixes

- [ ] [modules/Collection/src/IsEnumerable.php](https://github.com/elephox-dev/framework/tree/develop/modules/Collection/src/IsEnumerable.php)
  - [ ] de-duplicate code from IsEnumerable and IsKeyedEnumerable where possible (move iterator creation to trait and return self with created iterator)


### 🚧 Related issues

- [vimeo/psalm](https://github.com/vimeo/psalm)
  - [#7795](https://github.com/vimeo/psalm/issues/7795)

<!-- end annotations -->
