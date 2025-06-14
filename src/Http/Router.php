<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Errors\ClientError;
use Flytachi\Kernel\Src\Factory\Mapping\Mapping;
use Flytachi\Kernel\Src\Factory\Middleware\Cors\AccessControl;
use Flytachi\Kernel\Src\Stereotype\ControllerInterface;
use Psr\Log\LoggerInterface;

final class Router
{
    /**
     * An array to store registered routes in a tree structure.
     *
     * @var array
     */
    private static array $routes = [];
    private static LoggerInterface $logger;

    final public static function run(bool $isDevelop = false): void
    {
        self::$logger = Extra::$logger->withName(self::class);
        Header::setHeaders();
        self::route($isDevelop);
    }

    private static function route(bool $isDevelop = false): void
    {
        self::$logger->debug(
            'route: ' . $_SERVER['REQUEST_METHOD']
            . ' ' . $_SERVER['REQUEST_URI']
            . ' IP: ' . Header::getIpAddress()
        );
        $data = self::splitUrlAndParams($_SERVER['REQUEST_URI']);
        $_GET = $data['params'];

        $render = new Rendering();
        try {
            // registration
            self::registrar($isDevelop);

            $resolve = self::resolveActions($data['url']);
            if (!$resolve) {
                throw new ClientError(
                    "{$_SERVER['REQUEST_METHOD']} '{$data['url']}' url not found",
                    HttpCode::NOT_FOUND->value
                );
            }

            // options
            if ($_SERVER['REQUEST_METHOD'] == Method::OPTIONS->name) {
                AccessControl::processed($resolve['options']);
            }

            $resolve = self::resolveActionSelect($resolve, $_SERVER['REQUEST_METHOD']);
            if (!$resolve) {
                throw new ClientError(
                    "{$_SERVER['REQUEST_METHOD']} '{$data['url']}' url not found",
                    HttpCode::NOT_FOUND->value
                );
            }
            $result = self::callResolveAction($resolve['action'], $resolve['params'], $resolve['url'] ?? '');
            $render->setResource($result);
        } catch (\Throwable $e) {
            $render->setResource($e);
        }

        $render->render();
    }

    /**
     * Resolves a given URL and HTTP method to a registered route.
     *
     * This method searches the registered routes for a match to the provided URL and HTTP method.
     * If a match is found, it returns an array containing the associated controller action and any dynamic parameters.
     * If no match is found, it returns null.
     *
     * @param string $url The requested URL to resolve.
     * @return array|null Returns an array with the action and
     * parameters if a route is found, or null if no route matches.
     */
    final public static function resolveActions(string $url): ?array
    {
        $node = self::$routes;
        $params = [];
        $parts = explode('/', trim($url, '/'));

        // Traverse the route tree to find a match
        foreach ($parts as $part) {
            if (isset($node[$part])) {
                $node = $node[$part];
            } elseif (isset($node['{param}'])) {
                $node = $node['{param}'];
                $params[] = $part;
            } else {
                return null; // No matching route found
            }
        }

        return ['options' => $node, 'params' => $params];
    }

    final public static function resolveActionSelect(array $resolve, string $httpMethod): ?array
    {
        if (isset($resolve['options']['actions'][$httpMethod])) {
            return [
                'action' => $resolve['options']['actions'][$httpMethod],
                'params' => $resolve['params']
            ];
        }
        if (isset($resolve['options']['defaultAction'])) {
            return ['action' => $resolve['options']['defaultAction'], 'params' => $resolve['params']];
        }

        return null;
    }

    /**
     * Resolves a given URL and HTTP method to a registered route.
     *
     * This method searches the registered routes for a match to the provided URL and HTTP method.
     * If a match is found, it returns an array containing the associated controller action and any dynamic parameters.
     * If no match is found, it returns null.
     *
     * @param string $url The requested URL to resolve.
     * @param string $httpMethod The HTTP method used in the request (e.g., "GET").
     * @return array|null Returns an array with the action and
     * parameters if a route is found, or null if no route matches.
     */
    final public static function resolve(string $url, string $httpMethod): ?array
    {
        $node = self::$routes;
        $params = [];
        $parts = explode('/', trim($url, '/'));

        // Traverse the route tree to find a match
        foreach ($parts as $part) {
            if (isset($node[$part])) {
                $node = $node[$part];
            } elseif (isset($node['{param}'])) {
                $node = $node['{param}'];
                $params[] = $part;
            } else {
                return null; // No matching route found
            }
        }

        // Return the action and parameters if a match is found
        if (isset($node['actions'][$httpMethod])) {
            return ['action' => $node['actions'][$httpMethod], 'params' => $params];
        }
        if (isset($node['defaultAction'])) {
            return ['action' => $node['defaultAction'], 'params' => $params];
        }

        return null; // No action found for the route
    }

    private static function registrar(bool $isDevelop): void
    {
        if ($isDevelop) {
            if (file_exists(Extra::$pathFileMapping)) {
                unlink(Extra::$pathFileMapping);
            }
            $declaration = Mapping::scanningDeclaration();
            foreach ($declaration->getChildren() as $item) {
                self::request(
                    $item->getUrl(),
                    $item->getClassName(),
                    $item->getClassMethod(),
                    $item->getMiddlewareClassNames(),
                    $item->getMethod(),
                    $item->getMethodArgs()
                );
            }
        } else {
            if (!file_exists(Extra::$pathFileMapping)) {
                self::generateMappingRoutes();
            } else {
                self::$routes = require Extra::$pathFileMapping;
            }
        }
    }

    /**
     * Registers a route with the router.
     *
     * This method allows you to define a route, associate it with a controller class and method,
     * and optionally specify an HTTP method. The route can include dynamic parameters (e.g., `/user/{id}`).
     *
     * @param string $route The URL route pattern (e.g., "/user/{id}").
     * @param string $class The controller class to handle the route.
     * @param string $classMethod The method within the controller class to call (defaults to 'index').
     * @param array $middlewares
     * @param string|null $method The HTTP method for the route (e.g., 'GET', 'POST', ...).
     * If null, the route will be treated as a default action.
     * @param array $classMethodArgs
     * @return void
     * @throws RouterException If the route is already registered with the same HTTP method or as a default action.
     */
    private static function request(
        string $route,
        string $class,
        string $classMethod = 'index',
        array $middlewares = [],
        ?string $method = null,
        array $classMethodArgs = []
    ): void {
        // Normalize the URL by trimming slashes
        $route = trim($route, '/');
        $parts = explode('/', $route);

        // Build the route tree
        $node = &self::$routes;
        foreach ($parts as $part) {
            $isParam = preg_match('/^\{[a-zA-Z_][a-zA-Z0-9_]*}$/', $part) === 1;
            $key = $isParam ? '{param}' : $part;

            if (!isset($node[$key])) {
                $node[$key] = [];
            }
            $node = &$node[$key];
        }

        // Register middlewares
        if (!empty($middlewares)) {
            $duplicates = array_diff_assoc($middlewares, array_unique($middlewares));
            if (!empty($duplicates)) {
                $duplicatesList = implode(', ', $duplicates);
                throw new RouterException("Duplicate Middleware found: [{$duplicatesList}].");
            }
        }

        // Register the route with the specified HTTP method or as a default action
        if (!empty($method)) {
            if (isset($node['actions'][$method])) {
                throw new RouterException("Route '$route' with method '$method' is already registered.");
            }
            $node['actions'][$method] = [
                'class' => $class,
                'method' => $classMethod,
                'methodArgs' => $classMethodArgs,
                'middlewares' => $middlewares
            ];
        } else {
            if (isset($node['defaultAction'])) {
                throw new RouterException("Route '$route' (default) is already registered.");
            }
            $node['defaultAction'] = [
                'class' => $class,
                'method' => $classMethod,
                'methodArgs' => $classMethodArgs,
                'middlewares' => $middlewares
            ];
        }
    }

    final public static function generateMappingRoutes(): void
    {
        $declaration = Mapping::scanningDeclaration();
        foreach ($declaration->getChildren() as $item) {
            self::request(
                $item->getUrl(),
                $item->getClassName(),
                $item->getClassMethod(),
                $item->getMiddlewareClassNames(),
                $item->getMethod(),
                $item->getMethodArgs(),
            );
        }
        $mapString = var_export(json_decode(json_encode(self::$routes), true), true);
        $fileData = "<?php" . PHP_EOL . PHP_EOL;
        $fileData .= "/**" . PHP_EOL . " * Mapping configurations"
            . PHP_EOL . " * - Created on: " . date(DATE_RFC822)
            . PHP_EOL . " * - Version: 1.5"
            . PHP_EOL . " */" . PHP_EOL . PHP_EOL
            . "return {$mapString};";
        file_put_contents(Extra::$pathFileMapping, $fileData);
        if (function_exists('opcache_reset')) {
            try {
                opcache_reset();
            } catch (\Throwable $e) {
            }
        }
    }


    final protected static function splitUrlAndParams(string $url): array
    {
        $parsedUrl = parse_url($url);
        $urlWithoutParams = $parsedUrl['path'];
        $params = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $params);
        }

        return [
            'url' => $urlWithoutParams,
            'params' => $params
        ];
    }

    /**
     * @param array{class: class-string<ControllerInterface>, method: string} $action
     * @param array<int, string> $params
     * @param string $stringUrl
     * @return mixed
     * @throws RouterException|ClientError
     */
    final protected static function callResolveAction(array $action, array $params = [], string $stringUrl = ''): mixed
    {
        $controller = new $action['class']();
        $methods = get_class_methods($controller);

        if (!in_array($action['method'], $methods)) {
            throw new RouterException(
                "{$_SERVER['REQUEST_METHOD']} '{$stringUrl}' url realization '{$action['method']}' not found"
            );
        }

        if (isset($action['methodArgs'])) {
            foreach ($action['methodArgs'] as $key => $value) {
                if (!isset($params[$key])) {
                    continue;
                }

                if (!empty($value['typeInfo']) && !empty($value['typeInfo']['backing'])) {
                    settype($params[$key], $value['typeInfo']['backing']);
                    $params[$value['name']] = $value['typeInfo']['name']::from($params[$key]);
                } else {
                    $params[$value['name']] = $params[$key];
                }

                unset($params[$key]);
            }
        }

        try {
            $middlewares = [];
            foreach ($action['middlewares'] as $middlewareName) {
                $middleware = new $middlewareName();
                $middleware->optionBefore();
                $middlewares[] = $middleware;
            }

            $result = call_user_func_array([$controller, $action['method']], $params);

            foreach ($middlewares as $middleware) {
                $result = $middleware->optionAfter($result);
            }
            return $result;
        } catch (\ArgumentCountError | \TypeError $exception) {
            $trace = $exception->getTrace();

            if (
                isset($trace[1]['function'], $trace[1]['file']) &&
                $trace[1]['function'] === 'call_user_func_array' &&
                $trace[1]['file'] === __FILE__
            ) {
                $temp = $controller::class . "::" . $action['method'] . '()';
                throw new ClientError(
                    str_replace($temp, '', $exception->getMessage()),
                    HttpCode::BAD_REQUEST->value
                );
            } else {
                throw $exception;
            }
        }
    }
}
