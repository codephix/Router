<?php

declare(strict_types=1);

namespace CodePhix\Router;

interface RouteCollectionInterface
{
    public function delete(string $path, $handler);
    public function get(string $path, $handler);
    public function head(string $path, $handler);
    public function map(string $method, string $path, $handler);
    public function options(string $path, $handler);
    public function patch(string $path, $handler);
    public function post(string $path, $handler);
    public function put(string $path, $handler);
}
