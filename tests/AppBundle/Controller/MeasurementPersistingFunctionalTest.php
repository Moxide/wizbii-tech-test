<?php

namespace App\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;

class MeasurementPersistingFunctionalTest extends KernelTestCase
{
    /**
     * @var Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();
        $client = $kernel->getContainer()->get('test.client');
        $client->setServerParameters(array(
            'environment' => 'test',
        ),
        array(
            'HTTP_HOST' => 'host.tst',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0',
        ));
        $this->client = $client;
        $this->dm = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();
    }
    
    public function testPersisting() {
        // Vider la base de ses mesures
        $collection = $this->dm->getDocumentCollection('AppBundle:Measurement');
        $collection->remove([]);
        
        $client = $this->client;
        $crawler = $client->request('GET', '/collect?v=1&ds=web&tid=UA-1234-5&t=pageview&wuui=94760d4b3e58797ae1');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    
        $dm = $this->dm;
        $m = $dm->getRepository('AppBundle:Measurement')->findByTid('UA-1234-5');
        $this->assertCount(1, $m);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->dm->close();
        $this->dm = null; // avoid memory leaks
    }
}

