<?php

namespace CodePhix\Router;

class RouteUri
{
    /**
     * The route URI.
     *
     * @var string
     */
    public $uri;

    /**
     * The fields that should be used when resolving bindings.
     *
     * @var array
     */
    public $bindingFields = [];

    /**
     * The route URI.
     *
     * @var string
     */
    public static $domain;

    /**
     * Create a new route URI instance.
     *
     * @param  string  $uri
     * @param  array  $bindingFields
     * @return void
     */
    public function __construct(string $uri, ?array $bindingFields = [])
    {
        $this->uri = $uri;
        $this->bindingFields = $bindingFields;


        self::$domain =  !empty($_SERVER['HTTP_HOST'])
                ? str_replace(['http://', 'https://'], '', $_SERVER['HTTP_HOST']) : null;
    }

    /**
     * Parse the given URI.
     *
     * @param  string  $uri
     * @return static
     */
    public static function parse($uri = '')
    {

        if(empty($uri)){
            return new static($uri, []);
        }

        $bindingFields = [];
        
        preg_match_all("~\{\s* ([a-zA-Z_][a-zA-Z0-9_-]*) \}~x", $uri, $keys, PREG_SET_ORDER);
        if(!empty($keys)){
            $routeDiff = array_values(array_diff(explode("/", self::$domain), explode("/", $uri)));
            $route = str_replace('//','/',$uri);

            $Epatch = explode(".", self::$domain);
            $Eroute = explode(".", $route);

            $offset = 0;

            foreach ($keys as $key) {
                $bindingFields[$key[1]] = ($routeDiff[$offset++] ?? null);
            }
            $dominio = [];
            foreach($keys as $key2 => $p2){
                if(!empty($Eroute)){
                    foreach($Eroute as $k => $p){
                        if($p2[0] == $p){
                            $bindingFields[$p2[1]] = ((!empty($Epatch[$k])) ? $Epatch[$k] : '');
                        }else{
                            $dominio[] = $p;
                        }
                    }
                }
            }

            $uri = ((!empty($dominio)) ? implode('.',$dominio) : '' );
        }

        return new static($uri, $bindingFields);
    }
}
