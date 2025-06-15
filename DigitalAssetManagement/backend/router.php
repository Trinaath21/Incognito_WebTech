<?php
require __DIR__.'/config/cors.php';

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string and normalize path
if (($pos = strpos($request, '?')) !== false) {
    $request = substr($request, 0, $pos);
}

// Remove trailing slash if present
$request = rtrim($request, '/');

// Debug output
error_log("Request URI: " . $request);
error_log("Method: " . $method);

// Route API requests - improved matching
if (preg_match('#^/api/assets(?:/(\d+))?$#i', $request, $matches)) {
    if (isset($matches[1])) {
        $_GET['id'] = $matches[1]; // Set the ID if present
    }
    require __DIR__.'/api/assets.php';
    exit();
}

// Department-specific route
if (preg_match('#^/api/assets/(\d+)/department$#i', $request, $matches)) {
    $_GET['id'] = $matches[1];
    $_GET['department_endpoint'] = true;
    require __DIR__.'/api/assets.php';
    exit();
}

// Categories routes
if (preg_match('#^/api/categories$#i', $request)) {
    require __DIR__.'/api/categories.php';
    exit();
}

// Departments routes
if (preg_match('#^/api/departments$#i', $request)) {
    require __DIR__.'/api/department.php';
    exit();
}

// Not found handler
header('HTTP/1.1 404 Not Found');
header('Content-Type: application/json');
echo json_encode([
    'error' => 'Endpoint not found',
    'requested_path' => $request,
    'method' => $method,
    'debug_info' => [
        'matched_pattern' => 'none',
        'request_uri' => $_SERVER['REQUEST_URI'],
        'path_info' => $_SERVER['PATH_INFO'] ?? 'not available'
    ]
]);