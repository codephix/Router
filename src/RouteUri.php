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

    public $dominioAtual;
    public $dominio;

    /**
     * Create a new route URI instance.
     *
     * @param  string  $uri
     * @param  array  $bindingFields
     * @return void
     */
    public function __construct(string $uri, ?array $bindingFields = [], ?bool $dominioAtual = false, ?string $dominio = '')
    {
        $this->uri = $uri;
        $this->bindingFields = $bindingFields;
        $this->dominioAtual = $dominioAtual;
        $this->dominio = $dominio;

        $host = ((!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : null));

        self::$domain =  !empty($host) ? str_replace(['http://', 'https://'], '', $host) : null;
    }

    // Função para obter o subdomínio da URL
    public static function getSubdomain() {
        $host = $_SERVER['HTTP_HOST'];
        $hostParts = explode('.', $host);
        // if (count($hostParts) > 2) {
        if (count($hostParts) > 2) {
            return $hostParts[0];
        }
        return null;
    }

    // Função para verificar se o host é um subdomínio
    public static function isSubdomain($host, $domain) {
        // Domínio principal
        // $domain = 'semna.com.br';
        
        $hostParts = explode('.', $host);
        // if (count($hostParts) > 2) {

        if (count($hostParts) < 2)
            return null;

        // Verifica se o host termina com o domínio principal
        return (substr($host, -strlen($domain) - 1) === '.' . $domain);
    }

    /**
     * Parse the given URI.
     *
     * @param  string  $uri
     * @return static
     */
    public static function parse($uri = '')
    {

        $host = ((!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : null));

        $domain =  !empty($host) ? str_replace(['http://', 'https://'], '', $host) : null;
        $dominio = $uri;

        $dominioAtual = false;
        if(empty($uri)){
            return new static($uri, [], $dominioAtual);
        }

        $bindingFields = [];
        
        preg_match_all("~\{\s* ([a-zA-Z_][a-zA-Z0-9_-]*) \}~x", $uri, $keys, PREG_SET_ORDER);

        if(!empty($keys)){

            $routeDiff = array_values(array_diff(explode("/", $domain), explode("/", $uri)));
            $route = str_replace('//','/',$uri);

            $Epatch = explode(".", $domain);
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
            $dominio = ((!empty($dominio)) ? implode('.',$dominio) : '' );
        }

        $allow = false;
        if($dominio == $host){
            $allow = true;
        }else{

            if (!empty($bindingFields) && !empty($dominio) && self::isSubdomain($domain, $dominio)) {
                $allow = true;
            }elseif(!empty($bindingFields) && empty($dominio)){
                $allow = true;
                $bindingFields[$keys[0][1]] = $domain;
            }
        }

        return new static($uri, $bindingFields, $allow, $dominio);
    }
}
