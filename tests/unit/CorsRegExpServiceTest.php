<?php

use Nord\Lumen\Cors\CorsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Codeception\TestCase\Test;

class CorsRegExpServiceTest extends Test
{

    use Codeception\Specify;

    /**
     * @var CorsService
     */
    protected $service;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    public function testIsRequestAllowed()
    {
        $this->service = new CorsService;

        $this->request  = new Request;

        $this->specify('request is not allowed', function () {
            $this->request->headers->set('Origin', 'http://foo.com');

            verify($this->service->isRequestAllowed($this->request))->false();
        });

    }

    public function testIsRequestAllowedRegExp()
    {
        // RegExp is disabled as default, this should fail
        $this->service = new CorsService([
            'allowOrigins' => ["https?:\/\/.*-tv\.foo.com"],
        ]);

        $this->request  = new Request;

        $this->specify('request is not allowed', function () {
            $this->request->headers->set('Origin', 'http://t-tv.foo.com');
            verify($this->service->isRequestAllowed($this->request))->false();
        });

        // Enable RegExp support and add rules
        $this->service = new CorsService([
            'allowOriginsRegExp' => true,
            'allowOrigins' => [
                "https?:\/\/.*-tv\.foo.com",
                "http:\/\/.*yousee.dk",
                "https?:\/\/localhost:(10007|8443)"
            ]
        ]);

        $this->specify('request is allowed', function () {
            $this->request->headers->set('Origin', 'http://t-tv.foo.com');
            verify($this->service->isRequestAllowed($this->request))->true();

            $this->request->headers->set('Origin', 'http://s-tv.foo.com');
            verify($this->service->isRequestAllowed($this->request))->true();
            ;

            $this->request->headers->set('Origin', 'https://s-tv.foo.com');
            verify($this->service->isRequestAllowed($this->request))->true();

            $this->request->headers->set('Origin', 'http://sb8gdf87fd.yousee.dk');
            verify($this->service->isRequestAllowed($this->request))->true();

            $this->request->headers->set('Origin', 'http://www.yousee.dk');
            verify($this->service->isRequestAllowed($this->request))->true();

            $this->request->headers->set('Origin', 'http://www.yousee.dk');
            verify($this->service->isRequestAllowed($this->request))->true();

            $this->request->headers->set('Origin', 'http://localhost:10007');
            verify($this->service->isRequestAllowed($this->request))->true();

            $this->request->headers->set('Origin', 'https://localhost:8443');
            verify($this->service->isRequestAllowed($this->request))->true();
        });

        $this->specify('request is not allowed', function () {
            $this->request->headers->set('Origin', 'http://ttv.foo.com');
            verify($this->service->isRequestAllowed($this->request))->false();

            $this->request->headers->set('Origin', 'http://www.tdc.dk');
            verify($this->service->isRequestAllowed($this->request))->false();

            $this->request->headers->set('Origin', 'https://sb8gdf87fd.yousee.dk');
            verify($this->service->isRequestAllowed($this->request))->false();

            $this->request->headers->set('Origin', 'https://localhost:8080');
            verify($this->service->isRequestAllowed($this->request))->false();
        });

    }

}
