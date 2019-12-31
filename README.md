# Helper classes for Mezzio

[![Build Status](https://travis-ci.org/mezzio/mezzio-helpers.svg?branch=master)](https://travis-ci.org/mezzio/mezzio-helpers)

Helper classes for [Mezzio](https://github.com/mezzio/mezzio).

## Installation

Install this library using composer:

```bash
$ composer require mezzio/mezzio-helpers
```

We recommend using a dependency injection container, and typehint against
[container-interop](https://github.com/container-interop/container-interop). We
can recommend the following implementations:

- [laminas-servicemanager](https://github.com/laminas/laminas-servicemanager):
  `composer require laminas/laminas-servicemanager`
- [pimple-interop](https://github.com/moufmouf/pimple-interop):
  `composer require mouf/pimple-interop`
- [Aura.Di](https://github.com/auraphp/Aura.Di)

## Helpers Provided

### UrlHelper

`Mezzio\Helper\UrlHelper` provides the ability to generate a URI path
based on a given route defined in the `Mezzio\Router\RouterInterface`.
If registered as a route result observer, and the route being used was also
the one matched during routing, you can provide a subset of routing
parameters, and any not provided will be pulled from those matched.

In order to use the helper, you will need to instantiate it with the current
`RouterInterface`. The factory `Mezzio\Helper\UrlHelperFactory` has
been provided for this purpose, and can be used trivially with most
dependency injection containers implementing container-interop:

```php
use Mezzio\Helper\UrlHelper;
use Mezzio\Helper\UrlHelperFactory;

// laminas-servicemanager:
$services->setFactory(UrlHelper::class, UrlHelperFactory::class);

// Pimple:
$pimple[UrlHelper::class] = $pimple->share(function ($container) {
    $factory = new UrlHelperFactory();
    return $factory($container);
});

// Aura.Di:
$container->set(UrlHelperFactory::class, $container->lazyNew(UrlHelperFactory::class));
$container->set(
    UrlHelper::class,
    $container->lazyGetCall(UrlHelperFactory::class, '__invoke', $container)
);
```

The following dependency configuration will work for all three when using the
Mezzio skeleton:

```php
return ['dependencies' => [
    'factories' => [
        UrlHelper::class => UrlHelperFactory::class,
    ],
]]
```

> #### Factory requires RouterInterface
>
> The factory requires that a service named `Mezzio\Router\RouterInterface` is present,
> and will raise an exception if the service is not found.

For the helper to be useful, it must be injected with a
`Mezzio\Router\RouteResult`. To automate this, we provide a middleware
class, `UrlHelperMiddleware`, which accepts the `UrlHelper` instance and a
`Mezzio\Router\RouteResultSubjectInterface` instance (typically a
`Mezzio\Application` instance); when invoked, it attaches the
`UrlHelper` as a route result observer. To register this middleware, you
will need to:

- Register the `UrlHelperMiddleware` as a service in your container.
- Register the `UrlHelperMiddleware` as pre_routing pipeline middleware.

The following examples demonstrate registering the services.

```php
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\Helper\UrlHelperMiddlewareFactory;

// laminas-servicemanager:
$services->setFactory(UrlHelperMiddleware::class, UrlHelperMiddlewareFactory::class);

// Pimple:
$pimple[UrlHelperMiddleware::class] = $pimple->share(function ($container) {
    $factory = new UrlHelperMiddlewareFactory();
    return $factory($container);
});

// Aura.Di:
$container->set(UrlHelperMiddlewareFactory::class, $container->lazyNew(UrlHelperMiddlewareFactory::class));
$container->set(
    UrlHelperMiddleware::class,
    $container->lazyGetCall(UrlHelperMiddlewareFactory::class, '__invoke', $container)
);
```

To register the `UrlHelperMiddleware` as pre-routing pipeline middleware:

```php
use Mezzio\Helper\UrlHelperMiddleware;

// Do this early, before piping other middleware or routes:
$app->pipe(UrlHelperMiddleware::class);

// Or use configuration:
// [
//     'middleware_pipeline' => [
//         'pre_routing' => [
//             ['middleware' => UrlHelperMiddleware::class],
//         ],
//     ],
// ]
```

The following dependency configuration will work for all three when using the
Mezzio skeleton:

```php
return [
    'dependencies' => [
        'invokables' => [
        ],
        'factories' => [
            UrlHelper::class => UrlHelperFactory::class,
            UrlHelperMiddleware::class => UrlHelperMiddlewareFactory::class,
        ],
    ],
    'middleware_pipeline' => [
        'pre_routing' => [
            ['middleware' => UrlHelperMiddleware::class],
        ],
    ],
]
```


Compose the helper in your middleware (or elsewhere), and then use it to
generate URI paths:

```php
use Mezzio\Helper\UrlHelper;

class FooMiddleware
{
    private $helper;

    public function __construct(UrlHelper $helper)
    {
        $this->helper = $helper;
    }

    public function __invoke($request, $response, callable $next)
    {
        $response = $response->withHeader(
            'Link',
            $this->helper->generate('resource', ['id' => 'sha1'])
        );
        return $next($request, $response);
    }
}
```

You can use the methods `generate()` and `__invoke()` interchangeably (i.e., you
can use the helper as a function if desired). The signature is:

```php
function ($routeName, array $params = []) : string
```

Where:

- `$routeName` is the name of a route defined in the composed router. You may
  omit this argument if you want to generate the path for the currently matched
  request.
- `$params` is an array of substitutions to use for the provided route, with the
  following behavior:
  - If a `RouteResult` is composed in the helper, and the `$routeName` matches
    it, the provided `$params` will be merged with any matched parameters, with
    those provided taking precedence.
  - If a `RouteResult` is not composed, or if the composed result does not match
    the provided `$routeName`, then only the `$params` provided will be used 
    for substitutions.
  - If no `$params` are provided, and the `$routeName` matches the currently
    matched route, then any matched parameters found will be used.
    parameters found will be used.
  - If no `$params` are provided, and the `$routeName` does not match the
    currently matched route, or if no route result is present, then no
    substitutions will be made.

Each method will raise an exception if:

- No `$routeName` is provided, and no `RouteResult` is composed.
- No `$routeName` is provided, a `RouteResult` is composed, but that result
  represents a matching failure.
- The given `$routeName` is not defined in the router.

### ServerUrlHelper

`Mezzio\Helper\ServerUrlHelper` provides the ability to generate a full
URI by passing only the path to the helper; it will then use that path with the
current `Psr\Http\Message\UriInterface` instance provided to it in order to
generate a fully qualified URI.

In order to use the helper, you will need to inject it with the current
`UriInterface` from the request instance. To automate this, we provide
`Mezzio\Helper\ServerUrlMiddleware`, which composes a `ServerUrl`
instance, and, when invoked, injects it with the URI instance.

As such, you will need to:

- Register the `ServerUrlHelper` as a service in your container.
- Register the `ServerUrlMiddleware` as a service in your container.
- Register the `ServerUrlMiddleware` as pre_routing pipeline middleware.

The following examples demonstrate registering the services.

```php
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\ServerUrlMiddlewareFactory;

// laminas-servicemanager:
$services->setInvokableClass(ServerUrlHelper::class, ServerUrlHelper::class);
$services->setFactory(ServerUrlMiddleware::class, ServerUrlMiddlewareFactory::class);

// Pimple:
$pimple[ServerUrlHelper::class] = $pimple->share(function ($container) {
    return new ServerUrlHelper();
});
$pimple[ServerUrlMiddleware::class] = $pimple->share(function ($container) {
    $factory = new ServerUrlMiddlewareFactory();
    return $factory($container);
});

// Aura.Di:
$container->set(ServerUrlHelper::class, $container->lazyNew(ServerUrlHelper::class));
$container->set(ServerUrlMiddlewareFactory::class, $container->lazyNew(ServerUrlMiddlewareFactory::class));
$container->set(
    ServerUrlMiddleware::class,
    $container->lazyGetCall(ServerUrlMiddlewareFactory::class, '__invoke', $container)
);
```

To register the `ServerUrlMiddleware` as pre-routing pipeline middleware:

```php
use Mezzio\Helper\ServerUrlMiddleware;

// Do this early, before piping other middleware or routes:
$app->pipe(ServerUrlMiddleware::class);

// Or use configuration:
// [
//     'middleware_pipeline' => [
//         'pre_routing' => [
//             ['middleware' => ServerUrlMiddleware::class],
//         ],
//     ],
// ]
```

The following dependency configuration will work for all three when using the
Mezzio skeleton:

```php
return [
    'dependencies' => [
        'invokables' => [
            ServerUrlHelper::class => ServerUrlHelper::class,
        ],
        'factories' => [
            ServerUrlMiddleware::class => ServerUrlMiddlewareFactory::class,
        ],
    ],
    'middleware_pipeline' => [
        'pre_routing' => [
            ['middleware' => ServerUrlMiddleware::class],
        ],
    ],
]
```

Compose the helper in your middleware (or elsewhere), and then use it to
generate URI paths:

```php
use Mezzio\Helper\ServerUrlHelper;

class FooMiddleware
{
    private $helper;

    public function __construct(ServerUrlHelper $helper)
    {
        $this->helper = $helper;
    }

    public function __invoke($request, $response, callable $next)
    {
        $response = $response->withHeader(
            'Link',
            $this->helper->generate() . '; rel="self"'
        );
        return $next($request, $response);
    }
}
```

You can use the methods `generate()` and `__invoke()` interchangeably (i.e., you
can use the helper as a function if desired). The signature is:

```php
function ($path = null) : string
```

Where:

- `$path`, when provided, can be a string path to use to generate a URI.

## Documentation

See the [mezzio](https://github.com/mezzio/mezzio/blob/master/doc/book)
documentation tree, or browse online at http://mezzio.rtfd.org.
