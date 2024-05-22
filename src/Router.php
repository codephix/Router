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


}