<?php

namespace CodePhix\Router;

/**
 * Class CodePhix Router
 *
 * @author Robson V. Leite <https://github.com/robsonvleite>
 * @package CodePhix\Router
 */
class Router extends Dispatch
{

    private $Dispatch;
    /**
     * Router constructor.
     *
     * @param string $projectUrl
     * @param null|string $separator
     */
    public function __construct(string $projectUrl, ?string $separator = ":")
    {
        parent::__construct($projectUrl, $separator);
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public function post(string $route, $handler, string $name = null)
    {
        $this->addRoute("POST", $route, $handler, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public function get(string $route, $handler, string $name = null)
    {
        $this->addRoute("GET", $route, $handler, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public function put(string $route, $handler, string $name = null)
    {
        $this->addRoute("PUT", $route, $handler, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public function patch(string $route, $handler, string $name = null)
    {
        $this->addRoute("PATCH", $route, $handler, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public function delete(string $route, $handler, string $name = null)
    {
        $this->addRoute("DELETE", $route, $handler, $name);
        return $this;
    }

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public function options(string $route, $handler, string $name = null)
    {
        $this->addRoute("OPTIONS", $route, $handler, $name);
        return $this;
    }
    

    /**
     * @param string $route
     * @param $handler
     * @param string|null $name
     */
    public function head(string $route, $handler, string $name = null)
    {
        $this->addRoute("HEAD", $route, $handler, $name);
        return $this;
    }
    




    
    /**
     * Router constructor.
     *
     * @param string $projectUrl
     * @param null|string $separator
     */
    public function init(string $projectUrl, ?string $separator = ":")
    {
       $this->Dispatch = new Dispatch($projectUrl, $separator);
       return $this->Dispatch;
    }

    /**
     * @param null|string $namespace
     */
    // public function namespace(?string $namespace)
    // {
    //     $this->Dispatch->namespace($namespace);
    //     return $this->Dispatch;
    // }

    /**
     * @param null|string $group
     */
    public function groupMap(?string $group, $handler)
    {
        $this->Dispatch->group($group);
        $handler;
        return $this->Dispatch;
    }

    /**
     * Register a new route responding to all verbs.
     *
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \Illuminate\Routing\Route
     */
    public function any($uri, $action = null)
    {
        $this->Dispatch->any($uri, $action);
        return $this->Dispatch;
    }

    public function controller(string $controller, callable $action = null){

        $this->Dispatch->controller( $controller,  $action);
        return $this->Dispatch;
    }

    public function middleware($middleware, callable $action = null, ?string $namespace = ''){
        $this->Dispatch->middleware($middleware,  $action, $namespace);
        return $this->Dispatch;
    }

    /**
     * Get or set the domain for the route.
     *
     * @param  string|null  $domain
     * @return $this|string|null
     */
    public function domain(string|array $domains, callable $group)
    {
        $this->Dispatch->domain($domains, $group);
        return $this->Dispatch;
    }

    /**
     * Route a resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    public function resource($name, $controller, array $options = [])
    {   
        $this->Dispatch->resource($name, $controller, $options);
        return $this->Dispatch;
    }

    /**
     * Register an array of API resource controllers.
     *
     * @param  array  $resources
     * @param  array  $options
     * @return void
     */
    public function apiResources(array $resources, array $options = [])
    {
        $this->Dispatch->apiResources($resources,  $options);
        return $this->Dispatch;
    }

    /**
     * Route an API resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     */
    public function apiResource($name, $controller, ?array $options = [])
    {
        $this->Dispatch->apiResource($name,  $controller,$options);
        return $this->Dispatch;
    }

}