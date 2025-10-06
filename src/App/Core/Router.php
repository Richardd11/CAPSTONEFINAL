<?php

namespace App\Core;

class Router
{
    private $routes = [];

    /**
     * Add a GET route
     */
    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Add a POST route
     */
    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Add a PUT route
     */
    public function put($path, $callback)
    {
        $this->routes['PUT'][$path] = $callback;
    }

    /**
     * Add a DELETE route
     */
    public function delete($path, $callback)
    {
        $this->routes['DELETE'][$path] = $callback;
    }

    /**
     * Add routes for all HTTP methods
     */
    public function any($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
        $this->routes['POST'][$path] = $callback;
        $this->routes['PUT'][$path] = $callback;
        $this->routes['DELETE'][$path] = $callback;
    }

    /**
     * Handle the current request (legacy)
     */
    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }

        // Simplified path processing - don't strip subdirectories for built-in server
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $scriptDir = dirname($scriptName);
        
        // Only strip if we're actually in a subdirectory (not for built-in server)
        if ($scriptDir !== '/' && $scriptDir !== '.' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
            $path = $path === '' ? '/' : $path;
        }

        // Debug: Log the processed path
        error_log("=== ROUTER PATH PROCESSING ===");
        error_log("Original URI: " . $_SERVER['REQUEST_URI']);
        error_log("Original path: " . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        error_log("Script name: " . $scriptName);
        error_log("Script dir: " . $scriptDir);
        error_log("Processed path: " . $path);
        error_log("Request method: " . $method);
        error_log("Available routes: " . json_encode(array_keys($this->routes[$method] ?? [])));
        error_log("Route exists check: " . (isset($this->routes[$method][$path]) ? 'YES' : 'NO'));
        error_log("==============================");
        
        // Special debug for student exam routes
        if (strpos($path, '/student/exam/') === 0) {
            error_log("STUDENT EXAM ROUTE DEBUG: Requested path = $path");
            error_log("Available GET routes: " . json_encode(array_keys($this->routes['GET'] ?? [])));
        }

        // Check if route exists
        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];
            $this->executeCallback($callback);
        } else {
            // Check for parameterized routes
            $matchedRoute = $this->findParameterizedRoute($method, $path);
            if ($matchedRoute) {
                $this->executeCallback($matchedRoute['callback'], $matchedRoute['params']);
            } else {
                $this->notFound();
            }
        }
    }

    /**
     * Dispatch based on current globals and echo handler return
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = dirname($scriptName);
        $parentDir = rtrim(dirname($scriptDir), '/');
        $candidates = array_unique(array_filter([
            $scriptDir,
            $parentDir && substr($scriptDir, -7) === '/public' ? $parentDir : null,
        ]));
        foreach ($candidates as $base) {
            if ($base !== '/' && $base !== '.' && strpos($path, $base) === 0) {
                $path = substr($path, strlen($base));
                $path = $path === '' ? '/' : $path;
                break;
            }
        }

        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];
            $result = $this->invoke($callback, []);
            if ($result !== null) {
                echo $result;
            }
            return;
        }

        $matchedRoute = $this->findParameterizedRoute($method, $path);
        if ($matchedRoute) {
            $result = $this->invoke($matchedRoute['callback'], $matchedRoute['params']);
            if ($result !== null) {
                echo $result;
            }
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    /**
     * Find parameterized route
     */
    private function findParameterizedRoute($method, $path)
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route => $callback) {
            $pattern = $this->convertRouteToPattern($route);
            error_log("Checking route pattern: $route -> $pattern against path: $path");
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove the full match
                error_log("✅ Route matched! Params: " . json_encode($matches));
                return [
                    'callback' => $callback,
                    'params' => $matches
                ];
            }
        }

        return null;
    }

    /**
     * Convert route to regex pattern
     */
    private function convertRouteToPattern($route)
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    /**
     * Execute callback with parameters (legacy)
     */
    private function executeCallback($callback, $params = [])
    {
        if (is_callable($callback)) {
            call_user_func_array($callback, $params);
        } elseif (is_string($callback) && strpos($callback, '@') !== false) {
            // Handle Controller@method format
            list($controller, $method) = explode('@', $callback);
            
            if (class_exists($controller)) {
                $instance = new $controller();
                if (method_exists($instance, $method)) {
                    call_user_func_array([$instance, $method], $params);
                } else {
                    $this->notFound();
                }
            } else {
                $this->notFound();
            }
        } else {
            $this->notFound();
        }
    }

    /**
     * Invoke callback and return result
     */
    private function invoke($callback, array $params)
    {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }
        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($controller, $method) = explode('@', $callback);
            if (class_exists($controller)) {
                $instance = new $controller();
                if (method_exists($instance, $method)) {
                    return call_user_func_array([$instance, $method], $params);
                }
            }
        }
        return null;
    }

    /**
     * Handle 404 Not Found (legacy JSON)
     */
    private function notFound()
    {
        // Enhanced debugging for login issues
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        error_log("=== 404 ERROR DEBUG ===");
        error_log("Method: $method");
        error_log("Original Path: $path");
        error_log("Available routes for $method: " . json_encode(array_keys($this->routes[$method] ?? [])));
        error_log("All POST routes: " . json_encode(array_keys($this->routes['POST'] ?? [])));
        error_log("Request headers: " . json_encode(getallheaders()));
        error_log("========================");
        
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Route not found.',
            'debug' => [
                'method' => $method,
                'path' => $path,
                'available_routes' => array_keys($this->routes[$method] ?? [])
            ]
        ]);
    }

    /**
     * Get all registered routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}