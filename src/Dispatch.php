<?php

namespace CodePhix\Router;

//use App\Core\Reflector;
use CodePhix\Router\Reflector;
use Closure;

/**
 * Class CodePhix Dispatch
 *
 * @author Robson V. Leite <https://github.com/robsonvleite>
 * @package CodePhix\Router
 */
class Dispatch
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

    /** @var null|array */
    protected $dataError;

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


    protected $attributes = [];



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


    /**
     * All of the verbs supported by the router.
     *
     * @var string[]
     */
    public $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    private $padraoApiRecourse = [];

    private $only = [];

    /*

    case 'home':
        $this->addRoute("GET", $name, $controller.$this->separator.$only);
        break;
    case 'create':
        $this->addRoute(["GET","POST"], $name.'/'.$only, $controller.$this->separator.$only);
        break;
    case 'show':
        $this->addRoute(["GET"], $name.'/{uid}', $controller.$this->separator.$only);
        break;
    case 'edit':
        $this->addRoute(["PUT",'PATCH'], $name.'/{uid}/'.$only, $controller.$this->separator.$only);
        break;
    case 'destroy':
        $this->addRoute(['DELETE'], $name.'/{uid}', $controller.$this->separator.$only);
        break;

        */

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
        $this->action['domain'] = [];
        $this->PadraoApiRecourse();

    }

    public function getPadraoApiRecourse(){
        return $this->padraoApiRecourse;
    }

    public function setPadraoApiRecourse(?array $dados = []){
        if(!empty($dados)){
            foreach($dados as $padrao){
                if(!empty($padrao['action'])){
                    $this->padraoApiRecourse[] = [
                        'path' => ((!empty($padrao['path'])) ? $padrao['path'] : '/'),
                        'action' => $padrao['action'],
                        'metodo' => ((!empty($padrao['action'])) ? $padrao['action'] : $this->verbs),
                    ];
                }
            }
        }
        return $this;
    }

    public function PadraoApiRecourse(){
        $this->only = ['home','create','show','edit','update','destroy'];

        $this->padraoApiRecourse = [
            [
                'path' => '/home',
                'action' => 'home',
                'metodo' => ['GET'],
            ],
            [
                'path' =>  '/create',
                'action' => 'create',
                'metodo' => ["GET","POST"],
            ],
            [
                'path' =>  '/{uid}/ficha',
                'action' => 'show',
                'metodo' => ["GET"],
            ],
            [
                'path' =>  '/{uid}/edit',
                'action' => 'edit',
                'metodo' => ["GET"],
            ],
            [
                'path' =>  '/{uid}/update',
                'action' => 'update',
                'metodo' => ["PUT",'PATCH'],
            ],
            [
                'path' =>  '/{uid}/delete',
                'action' => 'destroy',
                'metodo' => ['DELETE'],
            ],
        ];
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

    public function user(callable|string|array $callable = null ): Dispatch{
        if (method_exists($callable, 'handle')) {
            $newController = new $callable;
            $newController->handle(($this->route['data'] ?? []));
            // return true;
        }
        return $this;
    }

    /**
     * @param string|array|null $group
     * @return Dispatch
     */
    public function group(string|array|null $group, ?callable $callable = null): Dispatch
    {

        // if(!is_string($group) && is_array($group) && empty($group['prefix'])){
        //     echo 'asd';
        //     return $this;
        // }

        if(!empty($group) && is_array($group) && !empty($group['domain'])){
            if(is_array($group['domain'])){
                foreach($group['domain'] as $domain){
                    $parsed = RouteUri::parse($domain);
                    if(empty($parsed->dominioAtual)){
                        return $this;
                    }
                }
            }else{
                $parsed = RouteUri::parse($group['domain']);
                if(empty($parsed->dominioAtual)){
                    return $this;
                }
            }
        }

        if(is_array($group)){
            if(!empty($group['prefix']) && is_string($group['prefix'])){
                $group = $group['prefix'];
            }
        }

        if(!empty($group) && is_string($group)){
            $inicio = mb_substr($group, 0, 1, 'UTF-8');
            if(strlen($group) > 1 && $inicio !== '/'){
                $group = '/'.$group;
            }
        }

        // $this->group = ($group ? str_replace("/", "", $group) : null);

        if(is_callable($callable)){
            $groupAtual = $this->group;


            $this->group .= '/'.((is_array($group) && !empty($group['prefix'])) ? $group['prefix'] : ( is_string($group) ? $group : null) ) ;

            $callable($this);

            $this->group = $groupAtual;
            unset($groupAtual);
            return $this;
        }

        $this->group = ($group ? $group : null);

        return $this;
    }

    /**
     * Register a new route responding to all verbs.
     *
     * @param  string  $uri
     * @param  array|string|callable|object|null  $action
     */
    public function any($uri, $action = null)
    {
        return $this->addRoute($this->verbs, $uri, $action);
    }
 /**
     * Compile the action into an array including the attributes.
     *
     * @param  \Closure|array|string|null  $action
     * @return array
     */
    protected function compileAction($action)
    {
        if (is_null($action)) {
            return $this->attributes;
        }

        if (is_string($action) || $action instanceof Closure) {
            $action = ['uses' => $action];
        }

        if (is_array($action) &&
            // ! Arr::isAssoc($action) &&
            Reflector::isCallable($action)) {
            if (strncmp($action[0], '\\', 1)) {
                $action[0] = '\\'.$action[0];
            }
            $action = [
                'uses' => $action[0].'@'.$action[1],
                'controller' => $action[0].'@'.$action[1],
            ];
        }

        return array_merge($this->attributes, $action);
    }

    public function match(?array $methods, string $uri, callable|string|array $action = null){

        // if (! is_array($action)) {
        //     $action = array_merge($this->attributes, $action ? ['uses' => $action] : []);
        // }

        return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
    }


    /**
     * Register an array of resource controllers.
     *
     * @param  array  $resources
     * @param  array  $options
     * @return void
     */
    public function resources(array $resources, array $options = [])
    {
        foreach ($resources as $name => $controller) {
            $this->resource($name, $controller, $options);
        }
    }

    /**
     * Route a resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     */
    public function resource($name, $controller, array $options = [])
    {

        if(!empty($this->padraoApiRecourse)){
            foreach($this->padraoApiRecourse as $padrao){
                if(in_array($padrao["action"], $options['only'])){
                    $this->addRoute($padrao['metodo'], $name.$padrao['path'], $controller.$this->separator.$padrao['action']);
                }
            }
        }
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
        foreach ($resources as $name => $controller) {
            $this->apiResource($name, $controller, $options);
        }
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
        $only = $this->only;

        if (isset($options['except'])) {
            $only = array_diff($only, (array) $options['except']);
        }

        return $this->resource($name, $controller, array_merge([
            'only' => $only,
        ], $options));
    }


    public function controller(string $controller, ?callable $action = null){

        $this->controller = $controller;

        $ControllerAnterior = $this->controller;

        $ex = explode('\\',$controller);
        if(empty($ex[1])){
           $controller = $this->namespace.'\\'.$controller;
        }


        $groups = $this->group;

        if(!is_null($action)){
            $action($this);
        }

        $this->group = $groups;

        // if(!empty($ControllerAnterior)){
        //     $this->controller = $ControllerAnterior;
        // }else{
        //     $this->controller = '';
        // }

        $this->controller = '';
    }

    public function middleware(string|array $middleware, callable $action = null, ?string $namespace = ''){
        // if(empty($namespace)){
        //     $namespace = $this->namespace;
        // }
        $this->middleware = [
            'middleware' => $middleware,
            'namespace' => $namespace,
        ];

        $groups = $this->group;

        if(!is_null($action)){
            $action($this);
        }

        $this->group = $groups;

        $this->middleware = '';
    }

    public function runMiddleware($middleware, $namespace){

        $middleware = [
            "handler" => $this->handler($middleware, $namespace),
            "action" => $this->action($middleware),
        ];

        if (is_callable($middleware['handler'])) {
            call_user_func($middleware['handler'], ($this->route['data'] ?? []));
        }

        $controller = $middleware['handler'];
        $method = $middleware['action'];
        if (class_exists($controller)) {
            /**
             * @var \Closure $newController
             */
            $newController = new $controller($this->route);
            if (method_exists($controller, $method)) {
                $newController->$method(($this->route['data'] ?? []));
            }else{
                $this->error = self::METHOD_NOT_ALLOWED;
                return true;
            }
        }else{
            $this->error = self::BAD_REQUEST;
            return true;
        }
    }

    public function map(?string $prefix, string $route = null, callable $group)
    {
        $groups = $this->group;


        if(!empty($prefix)){
            //$this->group = ($prefix ? $prefix.'/' : null).($route ? str_replace("/", "", $route) : null);
            $this->group = ($prefix ? $prefix.'/' : null).($route ? $route : null);
        }else{
            // $this->group = ($route ? str_replace("/", "", $route) : null);
            $this->group = ($route ? $route : null);
        }

        $this->group = str_replace("//","/",$this->group);

        $group($this);

        $this->group = $groups;
        return $this;
    }

    /**
     * Get or set the domain for the route.
     *
     * @param  string|null  $domain
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


        if(!empty($group) && is_array($group) && !empty($group['domain'])){
            $parsed = RouteUri::parse($group['domain']);
            if(empty($parsed->dominioAtual)){
                return $this;
            }
        }

        $alow = false;
        if(is_array($domains)){
            foreach($domains as $domain){
                $parsed = RouteUri::parse($domain);
                if(empty($parsed->dominioAtual)){
                    continue;
                }
                $alow = true;
                $this->action['domain'][] = $parsed->uri;
                if(!empty($parsed->bindingFields)){
                    $this->bindingFields = array_merge(
                        $this->bindingFields, $parsed->bindingFields
                    );
                }
            }
        }else{
            $parsed = RouteUri::parse($domains);
            if(empty($parsed->dominioAtual)){
                return $this;
            }
            $alow = true;
            $this->action['domain'][] = $parsed->uri;

            $this->bindingFields = array_merge(
                $this->bindingFields, $parsed->bindingFields
            );
        }

        if(empty($alow)){
            return $this;
        }

        $groups = $this->group;

        if(!is_null($group)){
            $group($this);
        }

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
        ? RouteUri::parse(str_replace(['http://', 'https://'], '', $_SERVER['HTTP_HOST']))->uri : null;
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
     * @return null|array
     */
    public function dataError(): ?array
    {
        return ['data' => $this->dataError];
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

            /*
            if(!empty($this->route['domain'])){
                $allow = false;
                foreach($this->route['domain'] as $domain){
                    if($domain == $this->getAcessDomain()){
                        $allow = true;
                    }
                }
                if(!$allow){
                    $this->error = self::NOT_FOUND;
                    return false;
                }
            }
            */

            if($this->route['middleware']){
                if(is_array($this->route['middleware']['middleware'])){
                    foreach($this->route['middleware']['middleware'] as $middleware){
                        $status = $this->runMiddleware($middleware, $this->route['middleware']['namespace']);
                        if($status){
                            return false;
                        }
                    }
                }else{
                    $status = $this->runMiddleware($this->route['middleware']['middleware'], $this->route['middleware']['namespace']);
                    if($status){
                        return false;
                    }
                }
            }

            if (is_callable($this->route['handler'])) {
                call_user_func($this->route['handler'], ($this->route['data'] ?? []));
                return true;
            }

            $controller = $this->route['handler'];
            $method = $this->route['action'];

            if (class_exists($controller)) {
                $newController = new $controller($this->route);
                if (method_exists($controller, $method)) {
                    $newController->$method(($this->route['data'] ?? []));
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
            $this->dataError = $this->data;
            return;
        }

        if ($this->httpMethod == "POST") {
            $this->data = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            unset($this->data["_method"]);
            $this->dataError = $this->data;
            return;
        }

        if (in_array($this->httpMethod, ["PUT", "PATCH", "DELETE"]) && !empty($_SERVER['CONTENT_LENGTH'])) {
            parse_str(file_get_contents('php://input', false, null, 0, $_SERVER['CONTENT_LENGTH']), $putPatch);
            $this->data = $putPatch;

            unset($this->data["_method"]);
            $this->dataError = $this->data;
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