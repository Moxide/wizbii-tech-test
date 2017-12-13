<?php

namespace AppBundle\Service;

class RateLimiter
{
    /*
     * @var array
     *
    */
    private $requests = array();
    
    public function checkRequest($requestString)
    {
        $requestSignature = md5($requestString);
        // time() en millisecondes
        $timestamp = round(microtime(true) * 1000);
        if(array_key_exists($requestSignature, $this->requests)) {
            if($timestamp < $this->requests[$requestSignature]) {
                return false;
            } else {
                unset($this->requests[$requestSignature]);
            }
        } else {
            // expiration 1 seconde plus tard
            $this->requests[$requestSignature] = $timestamp + 1 * 1000;
        }
        return true;
    }
}
