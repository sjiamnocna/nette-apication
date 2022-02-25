<?php declare(strict_types = 1);

namespace APIcation\Endpoints;

use APIcation\CRequest;
use Nette\Application\Response;
use Exception;
use Nette\SmartObject;
use function method_exists;
use function ucfirst;

/**
 * Base class for all Endpoints, executes methods on request as EResource/Action
 * Methods with '__' prefix can run only when we accessed with both Access token and service key
 * Methods with {$method}Prefix are executed for custom HTTP methods like `postMyAction` or `getMyAction`
 * Both things can be combined into __postMyAction for authorized POST request
 */
abstract class CAbstractEndpoint
{
    use SmartObject;
    
    /** @var array Neon configuration parameters */
    protected array $params;
    /**
     * Name services with capital letter first to distinguish
     * 
     * @var CRequest
     */
    protected CRequest $Request;

   /**
    * Global run method
    * Expects all methods to return any of Nette\Application\Response objects
    *
    * @param array     NEON Configuration parameters
    * @param CRequest $Request
    *
    * @return Response
    * @throws Exception If specified action doesn't exist
    */
    public function run(array $params, CRequest $Request): Response
    {
        // allows call to parent context and e.g. to get config
        $this->params = $params;
        // allow access to request in other methods
        $this->Request = $Request;

        // allow specific action for REST methods
        $method = strtolower($this->Request->getMethod());
        $action = $this->Request->getAction();
        $prefixedAction = $method . ucfirst($action);

        $priorityQueue = [];

        if ($Request->isAuthorized()){
            // prefix __ means private function accessible only with API key
            $priorityQueue[] = '__' . $prefixedAction;
            $priorityQueue[] = '__' . $action;
        }

        $priorityQueue[] = $prefixedAction;
        $priorityQueue[] = $action;

        foreach($priorityQueue as $action){
            // one by one call methods
            if (method_exists($this, $action)){
                return call_user_func([$this, $action]);
            }
        }
        
        throw new Exception('Action does not exist');
    }
}