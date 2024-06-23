<?php

namespace CodePhix\Router;

use Closure;
use ReflectionClass;
use ReflectionFunction;

/**
 * Class CodePhix Router
 *
 * @author Robson V. Leite <https://github.com/robsonvleite>
 * @package CodePhix\Router
 */
class Route
{
    
    /** @var Router */
    private static $Dispatch;

    /**
     * Router constructor.
     *
     * @param string $projectUrl
     * @param null|string $separator
     */
    public static function init(string $projectUrl, ?string $separator = ":")
    {
       self::$Dispatch = new Router($projectUrl, $separator);
       return self::$Dispatch;
    }

    /**
     * @param null|string $namespace
     */
    public static function namespace(?string $namespace)
    {
        self::$Dispatch->namespace($namespace);
        return self::$Dispatch;
    }

    /**
     * @param null|string $group
     */
    public static function user($handler = null )
    {
        self::$Dispatch->user($handler);
        return self::$Dispatch;
    }

    /**
     * @param null|string $group
     */
    public static function group(string|array|null $group, $handler = null )
    {
        self::$Dispatch->group($group, $handler);
        return self::$Dispatch;
    }

    /**
     * @param null|string $group
     */
    public static function groupMap(?string $group, $handler)
    {

        self::$Dispatch->group($group);
        $handler;
        return self::$Dispatch;
    }



    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public static function post(string $route, $handler, string $name = null)
    {
        self::$Dispatch->addRoute("POST", $route, $handler, $name);
        //return $this;
        return self::$Dispatch;
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public static function get(string $route, $handler, string $name = null)
    {
        self::$Dispatch->addRoute("GET", $route, $handler, $name);
        // return $this;
        return self::$Dispatch;
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public static function put(string $route, $handler, string $name = null)
    {
        self::$Dispatch->addRoute("PUT", $route, $handler, $name);
        // return $this;
        return self::$Dispatch;
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public static function patch(string $route, $handler, string $name = null)
    {
        self::$Dispatch->addRoute("PATCH", $route, $handler, $name);
        // return $this;
        return self::$Dispatch;
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public static function delete(string $route, $handler, string $name = null)
    {
        self::$Dispatch->addRoute("DELETE", $route, $handler, $name);
        // return $this;
        return self::$Dispatch;
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public static function options(string $route, $handler, string $name = null)
    {
        self::$Dispatch->addRoute("OPTIONS", $route, $handler, $name);
        // return $this;
        return self::$Dispatch;
    }
    

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public function head(string $route, $handler, string $name = null)
    {
        self::$Dispatch->addRoute("HEAD", $route, $handler, $name);
        // return $this;
        return self::$Dispatch;
    }

    /**
     * Register a new route responding to all verbs.
     *
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public static function any($uri, $action = null)
    {
        self::$Dispatch->any($uri, $action);
        return self::$Dispatch;
    }

    public static function match(?array $methods, string $uri, callable|string $action = null){
        self::$Dispatch->match( $methods, $uri, $action);
        return self::$Dispatch;
    }

    public static function controller(string $controller, callable $action = null){

        self::$Dispatch->controller( $controller,  $action);
        return self::$Dispatch;
    }

    public static function middleware($middleware, callable $action = null, ?string $namespace = ''){
        self::$Dispatch->middleware($middleware,  $action, $namespace);
        return self::$Dispatch;
    }
    
    public static function map(string|null $route, callable $group)
    {
        $prefix = ((!empty(self::$Dispatch->group)) ? self::$Dispatch->group : '');
        self::$Dispatch->map($prefix,  $route, $group);
        return self::$Dispatch;
    }

    /**
     * Get or set the domain for the route.
     *
     * @param  string|null  $domain
     * @return $this|string|null
     */
    public static function domain(string|array $domains, callable $group)
    {
        self::$Dispatch->domain($domains, $group);
        return self::$Dispatch;
    }

    /**
     * Route a resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public static function resource($name, $controller, array $options = [])
    {   
        self::$Dispatch->resource($name, $controller, $options);
        return self::$Dispatch;
    }

    /**
     * Register an array of API resource controllers.
     *
     * @param  array  $resources
     * @param  array  $options
     * @return void
     */
    public static function apiResources(array $resources, array $options = [])
    {
        self::$Dispatch->apiResources($resources,  $options);
        return self::$Dispatch;
    }

    /**
     * Route an API resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     */
    public static function apiResource($name, $controller, ?array $options = [])
    {
        self::$Dispatch->apiResource($name,  $controller,$options);
        return self::$Dispatch;
    }

    public static function dispatch(){
        self::$Dispatch->dispatch();
    }

    public static function getDataError(){
        return self::$Dispatch->dataError();
    }
    public static function error(){
        /**
        * ERROR REDIRECT
        */
        return self::$Dispatch->error();
    }


    public static function terminate(){
        /**
        * ERROR REDIRECT
        */
        if (self::$Dispatch->error()) {
            $data = [
                'errcode' => self::$Dispatch->error(),
            ];
            die;
        }
    }
}