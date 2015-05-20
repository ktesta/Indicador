<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */


class Oracle {
    private $url = false, $login, $password, $c, $cookiefile;
    public $error, $hasError = false, $response, $status;


    function __construct($url, $proxy, $login, $password) {
        $this->url      = $url;
        $this->login    = $login;
        $this->password = $password;
        $this->c = curl_init();

        //$this->cookiefile = tempnam('/tmp','api-hsm-cookie');
        curl_setopt($this->c, CURLOPT_URL, $url);
        curl_setopt($this->c, CURLOPT_PROXY, $proxy);
        curl_setopt($this->c, CURLOPT_POST, 1);
        curl_setopt($this->c, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($this->c, CURLOPT_SSL_VERIFYPEER, FALSE); // false for https
        //curl_setopt($this->c, CURLOPT_COOKIESESSION, 1);
        //curl_setopt($this->c, CURLOPT_COOKIEJAR, $this->cookiefile);
        curl_setopt($this->c, CURLOPT_RETURNTRANSFER, 1);
        
        //$this->autentica();
    }   

    function callAPI($method, $params, $auth) {

        $request = Array(
            'id'      => '1',
            'jsonrpc' => '2.0',
            'auth' => $auth,
            'method'  => $method,
            'params'  => $params
        );

        $this->hasError = false;
        $this->error    = "";

        curl_setopt($this->c, CURLOPT_POSTFIELDS, json_encode($request) );
        try {
            $return = curl_exec($this->c) or die("Error1");
            
            return $this->response = json_decode($return, 0);
        } catch(Exception $e) {
            $this->error = "Failed to call API: " . $e->getMessage();
            $this->hasError = true;
        }
    } 

    /*
    private function autentica() {
        $this->callAPI('auth', Array('username' => $this->login, 'password'  => $this->password));
    }
    */
    function __destruct() {
        curl_close($this->c);
        //unlink($this->cookiefile);
    }

}