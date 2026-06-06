<?php

namespace App\Core;

class Router
{
    protected $routes = [];

    public function add($method, $uri, $controller)
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller
        ];
    }

    public function get($uri, $controller)
    {
        $this->add('GET', $uri, $controller);
    }

    public function post($uri, $controller)
    {
        $this->add('POST', $uri, $controller);
    }

    public function dispatch($method, $uri)
    {
        $uri = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['uri'] === $uri) {
                return $this->executeAction($route['controller']);
            }
        }

        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
    }

    protected function executeAction($controllerAction)
    {
        if (is_callable($controllerAction)) {
            return $controllerAction();
        }

        [$controller, $action] = explode('@', $controllerAction);
        $controller = "App\\Controllers\\" . $controller;
        $controllerInstance = new $controller();
        return $controllerInstance->$action();
    }
}
