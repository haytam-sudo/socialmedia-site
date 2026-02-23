<?php

function url(string $path): string
{
    $path = '/' . ltrim($path, '/');
    return $path;
}


function public_url(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }

    // Absolute URLs are left untouched
    if (preg_match('#^(https?:)?//#i', $path)) {
        return $path;
    }
    // Ensure leading slash
    $path = '/' . ltrim($path, '/');

    return $path;
}

/**
 * Get the current request path (e.g. '/login', '/profile/1').
 */
function request_path(): string
{
    $url = $_GET['url'] ?? '';
    if ($url === '' || $url === '/') {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsed = parse_url($uri, PHP_URL_PATH);
        if ($parsed === null) {
            return '/';
        }
        $path = '/' . trim($parsed, '/');
        if ($path === '/' || $path === '/index.php') {
            return '/';
        }
        return $path;
    }
    $path = '/' . trim($url, '/');
    return $path === '/index.php' ? '/' : $path;
}

/**
 * Match a route pattern (e.g. '/profile/{id}') against path segments; extract params into $_GET.
 * Returns true if pattern matches.
 */
function match_route(string $pattern, string $path): bool
{
    $patternSegments = array_filter(explode('/', trim($pattern, '/')));
    $pathSegments = array_filter(explode('/', trim($path, '/')));
    if (count($patternSegments) !== count($pathSegments)) {
        return false;
    }
    foreach ($patternSegments as $i => $seg) {
        if (!isset($pathSegments[$i])) {
            return false;
        }
        if (strpos($seg, '{') === 0 && substr($seg, -1) === '}') {
            $param = substr($seg, 1, -1);
            $_GET[$param] = $pathSegments[$i];
            continue;
        }
        if ($seg !== $pathSegments[$i]) {
            return false;
        }
    }
    return true;
}

/** Route definitions: method and pattern => full path to include (from project root). */
function get_routes(): array
{
    $root = dirname(__DIR__);
    return [
        'GET /' => function () use ($root) {
            if (!empty($_SESSION['user_id'])) {
                header('Location: ' . url('/profile/' . (int)$_SESSION['user_id']));
                exit;
            }
            header('Location: ' . url('/login'));
            exit;
        },
        'GET /login' => $root . '/app/controller/loginController.php',
        'POST /login' => $root . '/app/controller/loginController.php',
        'GET /signup' => $root . '/app/controller/signupController.php',
        'POST /signup' => $root . '/app/controller/signupController.php',
        'GET /logout' => $root . '/app/controller/logout.php',
        'GET /profile/{id}' => $root . '/app/controller/profileController.php',
        'POST /profile/{id}' => $root . '/app/controller/profileController.php',
        'GET /profile/{id}/edit' => $root . '/app/controller/editprofileController.php',
        'POST /profile/{id}/edit' => $root . '/app/controller/editprofileController.php',
        'GET /search' => $root . '/app/controller/searchController.php',
        'POST /search' => $root . '/app/controller/searchController.php',
        'GET /notifications' => $root . '/app/controller/notificationsController.php',
        'POST /notifications' => $root . '/app/controller/notificationsController.php',
        'GET /explore' => $root . '/public/placeholder.php',
        'GET /messages' => $root . '/public/placeholder.php',
        'GET /settings' => $root . '/public/placeholder.php',
    ];
}

/**
 * Dispatch the current request: match method + path, set params, include handler.
 */
function dispatch(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = request_path();

    // Normalize placeholder routes so they receive ?page=
    $pageFromPath = [
        '/explore' => 'explore',
        '/notifications' => 'notifications',
        '/messages' => 'messages',
        '/settings' => 'settings',
    ];
    if (isset($pageFromPath[$path])) {
        $_GET['page'] = $pageFromPath[$path];
    }

    $routes = get_routes();
    $key = $method . ' ' . $path;

    // Exact match first
    if (isset($routes[$key])) {
        $handler = $routes[$key];
        if (is_callable($handler)) {
            $handler();
            return;
        }
        require $handler;
        return;
    }

    // Pattern match (e.g. /profile/123)
    foreach ($routes as $routeKey => $handler) {
        if (strpos($routeKey, '{') === false) {
            continue;
        }
        list($routeMethod, $pattern) = explode(' ', $routeKey, 2);
        if ($routeMethod !== $method) {
            continue;
        }
        if (match_route($pattern, $path)) {
            if (is_callable($handler)) {
                $handler();
                return;
            }
            require $handler;
            return;
        }
    }

    // 404
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404</title></head><body><h1>Not Found</h1><p><a href="' . htmlspecialchars(url('/')) . '">Home</a></p></body></html>';
}
