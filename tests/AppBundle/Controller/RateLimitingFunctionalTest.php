<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RateLimitingFunctionalTest extends WebTestCase
{
    public function testRateLimiting()
    {
        $client = static::createClient();

        // Requêtes identiques, intervalle de 0.5 seconde
        $client->request('GET', '/collect?v=1&ds=web&tid=UA-1234-5&t=pageview&wuui=94760d4b3e58797ae1');
        usleep(500000);
        $client->request('GET', '/collect?v=1&ds=web&tid=UA-1234-5&t=pageview&wuui=94760d4b3e58797ae1');
        $this->assertEquals(Response::HTTP_TOO_MANY_REQUESTS, $client->getResponse()->getStatusCode());

        // Requêtes identiques, intervalle de 1.5 seconde
        $client->request('GET', '/collect?v=1&ds=web&tid=UA-1234-5&t=pageview&wuui=94760d4b3e58797ae1');
        usleep(1500000);
        $client->request('GET', '/collect?v=1&ds=web&tid=UA-1234-5&t=pageview&wuui=94760d4b3e58797ae1');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        
        // Requêtes différentes, intervalle de 0.5 seconde
        $client->request('GET', '/collect?v=1&ds=web&tid=UA-1234-5&t=pageview&wuui=845151561161651655');
        usleep(500000);
        $client->request('GET', '/collect?v=1&ds=web&tid=UA-1234-5&t=pageview&wuui=94760d4b3e58797ae1');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
       

            
}
