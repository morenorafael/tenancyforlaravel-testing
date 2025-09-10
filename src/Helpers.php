<?php

if (! function_exists('route_tenant')) {
    function route_tenant(string $name, $parameters = [])
    {
        $route = route($name, $parameters);

        $routeComponent = parse_url($route);
        $scheme = $routeComponent['scheme'];

        $host = config('app.url');

        $baseUrl = str_replace("{$scheme}://", "{$scheme}://foo.", $host);
        $path = isset($routeComponent['path']) ? $routeComponent['path'] : '';

        return "{$baseUrl}{$path}";
    }
}

if (! function_exists('component')) {
    function component(string $component)
    {
        if (tenant()) {
            $component = "Tenant/{$component}";
        }

        return $component;
    }
}

if (! function_exists('route_name')) {
    function route_name(string $name)
    {
        if (tenant()) {
            $name = "tenant.{$name}";
        }

        return $name;
    }
}
