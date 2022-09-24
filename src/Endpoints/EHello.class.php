<?php declare(strict_types = 1);

namespace APIcation\Endpoints;

use APIcation\CResponse;
use Nette\Application\Responses\JsonResponse;

/**
 * Very useful and highly enjoyable endpoint that helps you get your "Hello" string
 */
class EHello extends CAbstractEndpoint
{

   /**
    * Endpoint accessible via calling to '/hello'
    *
    * @return CResponse
    */
    public function default(): CResponse
    {
       // accessible via calling to '/hello'
        return new CResponse('default', [
          'hello' => 'Hello World!'
        ]);
    }

   /**
    * Endpoint accessible via calling to '/hello' with service-key verified session
    * @return CResponse
    */
    public function __default(): CResponse
    {
        return new CResponse('default', [
          'hello' => 'Hello from the other side!'
        ]);
    }
}