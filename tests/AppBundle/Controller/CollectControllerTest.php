<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CollectControllerTest extends WebTestCase
{
    public function testV()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/collect?v=2');
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertContains('This value should be equal to 1.', $client->getResponse()->getContent());

    }
    
    public function testWct()
    {
        $client = static::createClient();
        
        // Good Mobile request
        $client->request(
            'GET',
            '/collect?v=1&wct=visitor&ds=web&tid=UA-1234-5&t=pageview&wui=94760d4b3e58797ae1',
            array(),
            array(),
            array(
                'HTTP_USER_AGENT'          => 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/4A93 Safari/419.3',
            )
        );
        
        // Bad mobile request
        $client->request(
            'GET',
            '/collect?v=1&ds=web&tid=UA-1234-5&t=pageview&wui=94760d4b3e58797ae1',
            array(),
            array(),
            array(
                'HTTP_USER_AGENT'          => 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/4A93 Safari/419.3',
            )
        );
        
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertContains("value should be 'profile', 'recruiter', 'visitor' or 'wizbii_employee'", $client->getResponse()->getContent());

        
        // Non mobile request
        $client->request(
            'GET',
            '/collect?v=1&ds=web&tid=UA-1234-5&t=pageview&wuui=94760d4b3e58797ae1',
            array(),
            array(),
            array(
                'HTTP_USER_AGENT'          => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0',
            )
        );
        
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
  
    }
    

            
}
