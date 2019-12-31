<?php

/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Helper;

use Mezzio\Router\Exception\RuntimeException as RouterException;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouteResultObserverInterface;
use Mezzio\Router\RouterInterface;

class UrlHelper implements RouteResultObserverInterface
{
    /**
     * @var RouteResult
     */
    private $result;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Generate a URL based on a given route.
     *
     * @param string $route
     * @param array $params
     * @return string
     * @throws Exception\RuntimeException if no route provided, and no result match
     *     present.
     * @throws Exception\RuntimeException if no route provided, and result match is a
     *     routing failure.
     * @throws RouterException if router cannot generate URI for given route.
     */
    public function __invoke($route = null, array $params = [])
    {
        $result = $this->getRouteResult();
        if ($route === null && $result === null) {
            throw new Exception\RuntimeException(
                'Attempting to use matched result when none was injected; aborting'
            );
        }

        if ($route === null) {
            return $this->generateUriFromResult($params, $result);
        }

        if ($this->result) {
            $params = $this->mergeParams($route, $result, $params);
        }

        return $this->router->generateUri($route, $params);
    }

    /**
     * Generate a URL based on a given route.
     *
     * Proxies to __invoke().
     *
     * @param string $route
     * @param array $params
     * @return string
     * @throws Exception\RuntimeException if no route provided, and no result match
     *     present.
     * @throws Exception\RuntimeException if no route provided, and result match is a
     *     routing failure.
     * @throws RouterException if router cannot generate URI for given route.
     */
    public function generate($route = null, array $params = [])
    {
        return $this($route, $params);
    }

    /**
     * {@inheritDoc}
     */
    public function update(RouteResult $result)
    {
        $this->result = $result;
    }

    /**
     * @param RouteResult $result
     */
    public function setRouteResult(RouteResult $result)
    {
        $this->result = $result;
    }

    /**
     * Internal accessor for retrieving the route result.
     *
     * @return null|RouteResult
     */
    protected function getRouteResult()
    {
        return $this->result;
    }

    /**
     * @param array $params
     * @param RouteResult $result
     * @return string
     * @throws RenderingException if current result is a routing failure.
     */
    private function generateUriFromResult(array $params, RouteResult $result)
    {
        if ($result->isFailure()) {
            throw new Exception\RuntimeException(
                'Attempting to use matched result when routing failed; aborting'
            );
        }

        $name   = $result->getMatchedRouteName();
        $params = array_merge($result->getMatchedParams(), $params);
        return $this->router->generateUri($name, $params);
    }

    /**
     * Merge route result params and provided parameters.
     *
     * If the route result represents a routing failure, returns the params
     * verbatim.
     *
     * If the route result does not represent the same route name requested,
     * returns the params verbatim.
     *
     * Otherwise, merges the route result params with those provided at
     * invocation, with the latter having precedence.
     *
     * @param string $route Route name.
     * @param RouteResult $result
     * @param array $params Parameters provided at invocation.
     * @return array
     */
    private function mergeParams($route, RouteResult $result, array $params)
    {
        if ($result->isFailure()) {
            return $params;
        }

        if ($result->getMatchedRouteName() !== $route) {
            return $params;
        }

        return array_merge($result->getMatchedParams(), $params);
    }
}
