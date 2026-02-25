<?php

function public_url(string $path): string
{
    $path = trim($path);
    if ($path === '' || preg_match('#^(https?:)?//#i', $path)) {
        return $path;
    }
    $path = preg_replace('#^/?(website/public)/#', '/', $path); //images are saved with website/public
    return '/' . ltrim($path, '/');
}
function request_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = '/' . trim(parse_url($uri, PHP_URL_PATH) ?? '/', '/');
    return ($path === '/' || $path === '/index.php') ? '/' : $path;
}

function match_route(string $pattern, string $path): bool
{
    $patternSegments = array_filter(explode('/', trim($pattern, '/')));
    $pathSegments    = array_filter(explode('/', trim($path, '/')));

    if (count($patternSegments) !== count($pathSegments)) {
        return false;
    }

    foreach ($patternSegments as $i => $seg) {
        if (strpos($seg, '{') === 0 && substr($seg, -1) === '}') {
            $_GET[substr($seg, 1, -1)] = $pathSegments[$i];
        } elseif ($seg !== $pathSegments[$i]) {
            return false;
        }
    }

    return true;
}

function get_routes(): array
{
    $root = dirname(__DIR__);
    return [
        'GET /'                  => function () {
            $dest = !empty($_SESSION['user_id']) ? '/profile/' . (int)$_SESSION['user_id'] : '/login';
            header('Location: ' . $dest);
            exit;
        },
        'GET /login'             => $root . '/app/controller/loginController.php',
        'POST /login'            => $root . '/app/controller/loginController.php',
        'GET /signup'            => $root . '/app/controller/signupController.php',
        'POST /signup'           => $root . '/app/controller/signupController.php',
        'GET /logout'            => $root . '/app/controller/logout.php',
        'GET /profile/{id}'      => $root . '/app/controller/profileController.php',
        'POST /profile/{id}'     => $root . '/app/controller/profileController.php',
        'GET /profile/{id}/edit' => $root . '/app/controller/editprofileController.php',
        'POST /profile/{id}/edit' => $root . '/app/controller/editprofileController.php',
        'GET /search'            => $root . '/app/controller/searchController.php',
        'POST /search'           => $root . '/app/controller/searchController.php',
        'GET /notifications'     => $root . '/app/controller/notificationsController.php',
        'POST /notifications'    => $root . '/app/controller/notificationsController.php',
        'GET /explore'           => $root . '/public/placeholder.php',
        'GET /messages'          => $root . '/public/placeholder.php',
        'GET /settings'          => $root . '/public/placeholder.php',
    ];
}

function dispatch(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path   = request_path();
    $routes = get_routes();
    $key    = $method . ' ' . $path;

    // Exact match
    if (isset($routes[$key])) {
        $handler = $routes[$key];
        is_callable($handler) ? $handler() : require $handler;
        return;
    }

    // Pattern match
    foreach ($routes as $routeKey => $handler) {
        if (strpos($routeKey, '{') === false) continue;
        [$routeMethod, $pattern] = explode(' ', $routeKey, 2);
        if ($routeMethod === $method && match_route($pattern, $path)) {
            is_callable($handler) ? $handler() : require $handler;
            return;
        }
    }

    // 404
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404</title></head><body><h1>Not Found</h1><p><a href="/">Home</a></p></body></html>';
}
