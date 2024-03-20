<?php

namespace CodePhix\Router;

/**
 * Class CodePhix Dispatch
 *
 * @author Robson V. Leite <https://github.com/robsonvleite>
 * @package CodePhix\Router
 */
abstract class Dispatch
{
    use RouterTrait;

    /** @var null|array */
    protected $route;

    /** @var bool|string */
    protected $projectUrl;

    /** @var string */
    protected $separator;

    /** @var null|string */
    protected $namespace;

    /** @var null|string */
    public $group;

    /** @var null|array */
    protected $data;

    /** @var int */
    protected $error;


    /**
     * @var ?string
     */
    protected $host;

    /**
     * @var ?string
     */
    protected $name;

    /**
     * @var ?int
     */
    protected $port;

    /**
     * @var ?string
     */
    protected $scheme;



    /** @const int Bad Request */
    public const BAD_REQUEST = 400;

    /** @const int Not Found */
    public const NOT_FOUND = 404;

    /** @const int Method Not Allowed */
    public const METHOD_NOT_ALLOWED = 405;

    /** @const int Not Implemented */
    public const NOT_IMPLEMENTED = 501;

    public const NOT_AUTHORITATIVE = 203;

    /**
     * The route controller array.
     *
     * @var array
     */
    public $controller;

    /**
     * The route middleware array.
     *
     * @var array
     */
    public $middleware;

    /**
     * The route action array.
     *
     * @var array
     */
    public $action;
    
    /**
     * The fields that implicit binding should use for a given parameter.
     *
     * @var array
     */
    protected $bindingFields = [];

    public $wheres;


    /**
     * All of the verbs supported by the router.
     *
     * @var string[]
     */
    public $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * Dispatch constructor.
     *
     * @param string $projectUrl
     * @param null|string $separator
     */
    public function __construct(string $projectUrl, ?string $separator = ":")
    {
        $this->projectUrl = (substr($projectUrl, "-1") == "/" ? substr($projectUrl, 0, -1) : $projectUrl);
        $this->patch = (filter_input(INPUT_GET, "route", FILTER_DEFAULT) ?? "/");
        $this->separator = ($separator ?? ":");
        $this->httpMethod = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return $this->routes;
    }

    /**
     * @param null|string $namespace
     * @return Dispatch
     */
    public function namespace(?string $namespace): Dispatch
    {
        $this->namespace = ($namespace ? ucwords($namespace) : null);
        return $this;
    }

    /**
     * @param null|string $group
     * @return Dispatch
     */
    public function group(?string $group): Dispatch
    {
        $this->group = ($group ? str_replace("/", "", $group) : null);
        return $this;
    }
    
    /*
    public function map(string $method, string $path, $handler, string $name = null)
    {
        $path  = sprintf('/%s', ltrim($path, '/'));
        $this->addRoute($method, $path, $handler, $name);
        return $this;
    }
    */



    /**
     * Register a new route responding to all verbs.
     *
     * @param  string  $uri
     * @param  array|string|callable|null  $action
     * @return \CodePhix\Router\Route
     */
    public function any($uri, $action = null)
    {
        return $this->addRoute($this->verbs, $uri, $action);
    }

    public function match(?array $methods, string $uri, callable|string $action = null){
        return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
    }

    /**
     * Register a new route responding to all verbs.
     *
     * @param  string  $uri
     * @param  array|string|callable|null|object  $action
     * @return \CodePhix\Router\Route
     */
    public function controller($controller, callable $action = null){

        $this->controller = $controller;

        $groups = $this->group;
        
        $action($this);

        $this->group = $groups;

        $this->controller = '';
    }

    public function middleware($middleware, callable $action = null, ?string $namespace = ''){

        if(empty($namespace)){
            $namespace = $this->namespace;
        }
        $this->middleware = [
            'middleware' => $middleware,
            'namespace' => $namespace,
        ];

        $groups = $this->group;
        
        $action($this);

        $this->group = $groups;

        $this->middleware = '';
    }
    
    public function map(?string $prefix, string $route, callable $group)
    {
        $groups = $this->group;
        if(!empty($prefix)){
            $this->group = ($prefix ? $prefix.'/' : null).($route ? str_replace("/", "", $route) : null);
        }else{
            $this->group = ($route ? str_replace("/", "", $route) : null);
        }

        $this->group = str_replace("//","/",$this->group);
        
        $group($this);

        $this->group = $groups;
        //$this->addRoute("GET", $prefix, $group);
        //$this->group = null;
        return $this;
    }

    /**
     * Get or set the domain for the route.
     *
     * @param  string|null|array  $domain
     * @return $this|string|null
     */
    public function domain(string|array $domains, callable $group)
    {
        if (is_null($domains)) {
            return $this->getDomain();
        }

        

        if(empty($this->action['domain'])){
            $this->action['domain'] = [];
        }

        if(is_array($domains)){
            foreach($domains as $domain){

                $parsed = RouteUri::parse($domain);
                $this->action['domain'][] = $parsed->uri;
            }
        }else{
            $parsed = RouteUri::parse($domains);
            $this->action['domain'][] = $parsed->uri;
        }


        // echo '<pre>';

        // print_r($parsed);
        // print_r($keys);

        // die;


        $this->bindingFields = array_merge(
            $this->bindingFields, $parsed->bindingFields
        );

        $groups = $this->group;
        
        $group($this);

        $this->group = $groups;

        $this->action['domain'] = '';


        //$this->addRoute("GET", $prefix, $group);
        //$this->group = null;
        return $this;
    }

    public function removeDomain(callable $group){

        $groups = $this->group;


        $group($this);
        $this->action['domain'] = '';

        $this->group = $groups;
        //$this->addRoute("GET", $prefix, $group);
        //$this->group = null;
        return $this;
    }

    public function getAcessDomain()
    {
        return !empty($_SERVER['HTTP_HOST'])
                ? str_replace(['http://', 'https://'], '', $_SERVER['HTTP_HOST']) : null;
    }

    /**
     * Get the domain defined for the route.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return isset($this->action['domain'])
                ? str_replace(['http://', 'https://'], '', $this->action['domain']) : null;
    }


    /**
     * Set a regular expression requirement on the route.
     *
     * @param  array|string  $name
     * @param  string|null  $expression
     * @return $this
     */
    public function where($name, $expression = null)
    {
        foreach ($this->parseWhere($name, $expression) as $name => $expression) {
            $this->wheres[$name] = $expression;
        }

        return $this;
    }

    /**
     * Parse arguments to the where method into an array.
     *
     * @param  array|string  $name
     * @param  string  $expression
     * @return array
     */
    protected function parseWhere($name, $expression)
    {
        return is_array($name) ? $name : [$name => $expression];
    }

    /**
     * Set a list of regular expression requirements on the route.
     *
     * @param  array  $wheres
     * @return $this
     */
    public function setWheres(array $wheres)
    {
        foreach ($wheres as $name => $expression) {
            $this->where($name, $expression);
        }

        return $this;
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  array  $attributes
     * @param  \Closure|string  $routes
     * @return void
     */
    public static function group_api(array $attributes, $routes)
    {
        //$this->updateGroupStack($attributes);

        // Once we have updated the group stack, we'll load the provided routes and
        // merge in the group's attributes when the routes are created. After we
        // have created the routes, we will pop the attributes off the stack.
        //$this->loadRoutes($routes);

        //array_pop($this->groupStack);
    }




    /**
     * @return null|array
     */
    public function data(): ?array
    {
        return $this->data;
    }

    /**
     * @return null|int
     */
    public function error(): ?int
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function dispatch(): bool
    {
        if (empty($this->routes) || empty($this->routes[$this->httpMethod])) {
            $this->error = self::NOT_IMPLEMENTED;
            return false;
        }

        $this->route = null;
        foreach ($this->routes[$this->httpMethod] as $key => $route) {
            if (preg_match("~^" . $key . "$~", $this->patch, $found)) {
                $this->route = $route;
            }
        }

        return $this->execute();
    }

    /**
     * @return bool
     */
    private function execute()
    {
        
        if ($this->route) {
            if(!empty($this->route['domain']['domain'])){
                $allow = false;
                foreach($this->route['domain']['domain'] as $domain){
                    if($domain == $this->getAcessDomain()){
                        $allow = true;
                    }
                }
                if(!$allow){
                    $this->error = self::NOT_FOUND;
                    return false;
                }
             
            }

            // echo '<pre>';

            // print_r($this->route);
            
            // $var = [
            //     $this->handler($this->route['middleware']['middleware'], $this->route['middleware']['namespace']),
            //     $this->action($this->route['middleware']['middleware']),
            // ];

            // print_r($var);

            // print_r(Reflector::isCallable($var, false));

            // die;

            if (is_callable($this->route['handler'])) {
                call_user_func($this->route['handler'], ($this->route['data'] ?? []));
                return true;
            }
            
            if($this->route['middleware']){

                $middleware = [
                    "handler" => $this->handler($this->route['middleware']['middleware'], $this->route['middleware']['namespace']),
                    "action" => $this->action($this->route['middleware']['middleware']),
                ];

                if (is_callable($middleware['handler'])) {
                    call_user_func($middleware['handler'], ($this->route['data'] ?? []));
                }

                
                $controller = $middleware['handler'];
                $method = $middleware['action'];
                
                if (is_object($middleware['handler'])) {
                    $controller->$method(($this->route['data'] ?? []));
                    //call_user_func($middleware['handler'], ($this->route['data'] ?? []));
                }else{

                    if (class_exists($controller)) {
                        if ($this->initClass($controller , $method)) {
                        }else{
                            $this->error = self::METHOD_NOT_ALLOWED;
                            return false;
                        }
                    }else{
                        $this->error = self::BAD_REQUEST;
                        return false;
                    }
                }
                //$middleware = $this->handler($this->route['middleware'], $this->route[]);

            }

            $controller = $this->route['handler'];
            $method = $this->route['action'];

            if (is_object($controller)) {
                if ($this->initClass($controller , $method)) {
                    return true;
                }
                $this->error = self::METHOD_NOT_ALLOWED;
                return false;
                //call_user_func($middleware['handler'], ($this->route['data'] ?? []));
            }


            if (class_exists($controller)) {
                if ($this->initClass($controller , $method)) {
                    return true;
                }
                $this->error = self::METHOD_NOT_ALLOWED;
                return false;
            }

            $this->error = self::BAD_REQUEST;
            return false;
        }
        $this->error = self::NOT_FOUND;
        return false;
    }


    private function initClass($controller , $method){
        $newController = new $controller($this);
        if (method_exists($controller, $method)) {
            $newController->$method(($this->route['data'] ?? []));
            return true;
        }
        $this->error = self::METHOD_NOT_ALLOWED;
        return false;

    }

    /**
     * httpMethod form spoofing
     */
    protected function formSpoofing(): void
    {
        $post = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (!empty($post['_method']) && in_array($post['_method'], ["PUT", "PATCH", "DELETE"])) {
            $this->httpMethod = $post['_method'];
            $this->data = $post;

            unset($this->data["_method"]);
            return;
        }

        if ($this->httpMethod == "POST") {
            $this->data = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            unset($this->data["_method"]);
            return;
        }

        if (in_array($this->httpMethod, ["PUT", "PATCH", "DELETE"]) && !empty($_SERVER['CONTENT_LENGTH'])) {
            parse_str(file_get_contents('php://input', false, null, 0, $_SERVER['CONTENT_LENGTH']), $putPatch);
            $this->data = $putPatch;

            unset($this->data["_method"]);
            return;
        }

        $this->data = [];
        return;
    }


    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function setHost(string $host)
    {
        $this->host = $host;
        return $this->checkAndReturnSelf();
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this->checkAndReturnSelf();
    }

    public function setPort(int $port)
    {
        $this->port = $port;
        return $this->checkAndReturnSelf();
    }

    public function setScheme(?string $scheme)
    {
        $this->scheme = $scheme;
        return $this->checkAndReturnSelf();
    }

    private function checkAndReturnSelf()
    {
        return $this;
    }

    protected function isExtraConditionMatch(): bool
    {
        // check for scheme condition
        $scheme = $this->getScheme();
        if ($scheme !== null && $scheme !== $this->getUri()->getScheme()) {
            return false;
        }
        // check for domain condition
        $host = $this->getHost();
        if ($host !== null && $host !== $this->getUri()->getHost()) {
            return false;
        }
        // check for port condition
        $port = $this->getPort();
        return !($port !== null && $port !== $this->getUri()->getPort());
    }

    private function getUri(){
        return (new Request);
    }

}