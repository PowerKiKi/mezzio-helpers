# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.3.0 - 2015-12-22

### Added

- [zendframework/zend-expressive-helpers#3](https://github.com/zendframework/zend-expressive-helpers/pull/3) and
  [zendframework/zend-expressive-helpers#5](https://github.com/zendframework/zend-expressive-helpers/pull/5) add
  a new `Mezzio\Helper\BodyParams\BodyParamsMiddleware` for use in
  parsing the request body. The middleware consumes strategies, which match
  against the `Content-Type` header, and, if matched, parse the body and return
  a new request with the parsed body parameters.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.2 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.1 - 2015-12-22

### Added

- [zendframework/zend-expressive-helpers#4](https://github.com/zendframework/zend-expressive-helpers/pull/4) adds the
  protected method `getRouteResult()`, providing extending classes access to the
  private `$result` member.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.0 - 2015-12-08

### Added

- [zendframework/zend-expressive-helpers#2](https://github.com/zendframework/zend-expressive-helpers/pull/2) adds the
  following classes:
  - `UrlHelperMiddleware`, which accepts a `UrlHelper` instance and a
    `RouteResultSubjectInterface` instance; during invocation, it attaches the
    helper to the subject as an observer.
  - `UrlHelperMiddlewareFactory`, which creates a `UrlHelperMiddleware` instance
    from the registered `UrlHelper` and `RouteResultSubjectInterface` (or
    `Application`) instances.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-expressive-helpers#2](https://github.com/zendframework/zend-expressive-helpers/pull/2) removes
  registration of the generated `UrlHelper` with the `Application` instance
  within the `UrlHelperFactory`. This change was made to observed race
  conditions when the `UrlHelper` is created within the context of the
  `ApplicationFactory` (e.g., when generating a `TemplatedErrorHandler`
  instance).

### Fixed

- Nothing.

## 1.1.0 - 2015-12-06

### Added

- [zendframework/zend-expressive-helpers#1](https://github.com/zendframework/zend-expressive-helpers/pull/1) adds a
  dependency on mezzio/mezzio-router, which replaces the
  dependency on zendframework/zend-expressive. This change means the component
  can be used without Expressive, and also removes a potential circular
  dependency issue in consumers of the package.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-expressive-helpers#1](https://github.com/zendframework/zend-expressive-helpers/pull/1) removes
  the mezzio/mezzio, replacing it with a dependency on
  mezzio/mezzio-router.

### Fixed

- Nothing.

## 1.0.0 - 2015-12-04

Initial release.

### Added

- `Mezzio\Helper\UrlHelper` provides the ability to generate a URI path
  based on a given route defined in the `Mezzio\Router\RouterInterface`.
  If registered as a route result observer, and the route being used was also
  the one matched during routing, you can provide a subset of routing
  parameters, and any not provided will be pulled from those matched.
- `Mezzio\Helper\ServerUrlHelper` provides the ability to generate a
  full URI by passing only the path to the helper; it will then use that path
  with the current `Psr\Http\Message\UriInterface` instance provided to it in
  order to generate a fully qualified URI.
- `Mezzio\Helper\ServerUrlMiddleware` is pipeline middleware that can
  be registered with an application; it will inject a `ServerUrlHelper` instance
  with the URI composed in the provided `ServerRequestInterface` instance.
- The package also provides factories compatible with container-interop that can
  be used to generate instances.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
