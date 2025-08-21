<?php
return function ($app): void {
    // Define API routes here. This file is responsible for registering all API endpoints.
    // Get the request URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';

    // Map route prefixes to their router files
    $routeMap = [
        '/v1/users' =>  '/v1/UsersRoute.php',
        '/v1/logs' =>  '/v1/ActivityLogsRoute.php',
        '/logs-viewer' =>  '/v1/LogsViewerRoute.php'
        // Add more routes as needed
    ];

    $loaded = false;
    // Check if the request matches any of the defined prefixes
    foreach ($routeMap as $prefix => $routerFile) {
        if (strpos($requestUri, $prefix) === 0) {
            // Load only the matching router
            if (file_exists(ROUTE . $routerFile)) {
                (require_once ROUTE . $routerFile)($app);
                $loaded = true;
            }
        }
    }

    // If no specific router was loaded, load all routers as fallback
    if (!$loaded) {
        // foreach ($routeMap as $routerFile) {
        //     if (file_exists($routerFile)) {
        //         (require_once $routerFile)($app);
        //     }
        // }
    };
};
