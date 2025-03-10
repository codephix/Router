<?php

namespace CodePhix\Router;

trait RouterTrait
{
    /** @var array */
    protected $routes;

    /** @var string */
    protected $patch;

    /** @var string */
    protected $httpMethod;

    /**
     * @param string $name
     * @param array|null $data
     * @return string|null
     */
    public function route(string $name, array $data = null): ?string
    {
        foreach ($this->routes as $http_verb) {
            foreach ($http_verb as $route_item) {
                if (!empty($route_item["name"]) && $route_item["name"] == $name) {
                    return $this->treat($route_item, $data);
                }
            }
        }
        return null;
    }

    /**
     * @param string $route
     * @param array|null $data
     */
    public function redirect(string $route, array $data = null): void
    {
        if ($name = $this->route($route, $data)) {
            header("Location: {$name}");
            exit;
        }

        if (filter_var($route, FILTER_VALIDATE_URL)) {
            header("Location: {$route}");
            exit;
        }

        $route = (substr($route, 0, 1) == "/" ? $route : "/{$route}");
        header("Location: {$this->projectUrl}{$route}");
        exit;
    }

    /**
     * @param string $method
     * @param string $route
     * @param string|callable $handler
     * @param null|string
     */
    public function addRoute(string|array $methods, string $route, $handler, string $name = null): void
    {

        if(!empty($this->controller) && is_string($handler)){
            $ex = explode($this->separator,$handler);
            if(!isset($ex[1])){
                $handler = $this->controller.$this->separator.$handler;
            }
        }

        if(is_array($methods)){
            foreach($methods as $method){
                $this->addRoute($method, $route, $handler, $name);
            }
            return;
        }

        $method = (string) $methods;

        if (false === $this->isExtraConditionMatch()) {
            return;
        }

        if (false === $this->isExtraConditionMatch($route)) {
            return;
        }

        if ($route == "/") {
            $this->addRoute($method, "", $handler, $name);
        }

        if(!empty($route) && is_string($route)){
            $inicio = mb_substr($route, 0, 1, 'UTF-8');
            if(strlen($route) > 1 && $inicio !== '/'){
                $route = '/'.$route;
            }
        }

        $route = (!$this->group ? $route : "/{$this->group}{$route}");
        $route = str_replace('//','/',$route);

        preg_match_all("~\{\s* ([a-zA-Z_][a-zA-Z0-9_-]*) \}~x", $route, $keys, PREG_SET_ORDER);
        $routeDiff = array_values(array_diff(explode("/", $this->patch), explode("/", $route)));

        $route = str_replace('//','/',$route);

        $Epatch = explode("/", $this->patch);
        $Eroute = explode("/", $route);

        $this->formSpoofing();
        $offset = ($this->group ? 1 : 0);

        foreach ($keys as $key) {
            $this->data[$key[1]] = ($routeDiff[$offset++] ?? null);
        }

        if(!empty($keys)){
            foreach($keys as $key2 => $p2){
                if(!empty($Eroute)){
                    foreach($Eroute as $k => $p){
                        if($p2[0] == $p){
                            $this->data[$p2[1]] = ((!empty($Epatch[$k])) ? $Epatch[$k] : '');
                        }
                    }
                }
            }
        }

        $data = $this->data;
        $namespace = $this->namespace;

        if(!empty($this->bindingFields)){
            if(empty($data)){
                $data = $this->bindingFields;
            }else{
                $data = array_merge(
                    $data, $this->bindingFields
                );
            }
        }

        $this->dataError = $data;

        $router = function () use ($method, $handler, $data, $route, $name, $namespace) {
            return [
                "route" => $route,
                "name" => $name,
                "method" => $method,
                "handler" => $this->handler($handler, $namespace),
                "action" => $this->action($handler),
                "data" => $data,
                "domain" => $this->action['domain'],
                "controller" => $this->controller,
                "middleware" => $this->middleware,
            ];
        };
        $route = preg_replace('~{([^}]*)}~', "([^/]+)", $route);
        $this->routes[$method][$route] = $router();
    }

    /**
     * @param $handler
     * @param $namespace
     * @return string|callable
     */
    private function handler($handler, $namespace)
    {
        return (!is_string($handler) ? $handler : "{$namespace}\\" . explode($this->separator, $handler)[0]);
    }

    /**
     * @param $handler
     * @return null|string
     */
    private function action($handler): ?string
    {
        return (!is_string($handler) ?: (explode($this->separator, $handler)[1] ?? null));
    }

    /**
     * @param array $route_item
     * @param array|null $data
     * @return string|null
     */
    private function treat(array $route_item, array $data = null): ?string
    {
        $route = $route_item["route"];
        if (!empty($data)) {
            $arguments = [];
            $params = [];
            foreach ($data as $key => $value) {
                if (!strstr($route, "{{$key}}")) {
                    $params[$key] = $value;
                }
                $arguments["{{$key}}"] = $value;
            }
            $route = $this->process($route, $arguments, $params);
        }

        return "{$this->projectUrl}{$route}";
    }

    /**
     * @param string $route
     * @param array $arguments
     * @param array|null $params
     * @return string
     */
    private function process(string $route, array $arguments, array $params = null): string
    {
        $params = (!empty($params) ? "?" . http_build_query($params) : null);
        return str_replace(array_keys($arguments), array_values($arguments), $route) . "{$params}";
    }

}