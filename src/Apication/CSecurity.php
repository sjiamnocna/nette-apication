<?php

declare(strict_types=1);

namespace APIcation;

use APIcation\CResponse;
use \Exception;
use Nette\Http\Request;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Schema\Expect;
use Nette\Utils\Random;

/**
 * CSecurity
 * secures API by serviceName and serviceKey for authorized actions
 * 
 * It's quite easy to rewrite with no dependencies from Nette Framework
 * 
 * @author sjiamnocna
 * @year 2022
 * @package nette-minimal
 */
class CSecurity
{
    /**
     * 1. client goes to Chk/init with serviceName header
     *  There he obtains the AccessKey
     * 2. Now he can access to non-protected functions or
     *  Authorize using Chk/authorize
     */
    const ENDPOINT_NAME = 'Chk';
    const HEADER_SERVICE_NAME = 'X-SERVICE-NAME';
    const HEADER_SERVICE_KEY = 'X-SERVICE-KEY';
    const HEADER_ACCESS_KEY = 'X-ACCESS-KEY';

    /** @var SessionSection Current session data */
    private SessionSection $Section;

    /** @var array List of services and its password */
    private array $services;

    /** @var bool Request is verified via access key */
    private bool $requestAuthenticated = false;

    public function __construct(
        array $services,
        Session $Session
    ) {
        // get session data or empty array
        $this->Section = $Session->getSection(self::class);

        if (method_exists($this, 'loadServices')){
            $services = array_merge($services, $this->loadServices);
        }

        $this->services = $services;
    }

    /**
     * First phase: Create access token based on service name
     * 
     * @param   string  Service name
     */
    private function serviceInit(string $serviceName): ?string
    {
        if (!isset($this->services[$serviceName])) {
            // unknown serviceName
            return null;
        }

        if ($this->getSessionProp('serviceName') != $serviceName) {
            bdump($serviceName, 'setting');
            // save service name and generate access key
            $this->setSessionProp('serviceName', $serviceName);
        }

        return $this->createAccessKey($serviceName);
    }

    /**
     * Create and save new access key
     * 
     * @param string Service name
     * @return string New access key
     */
    private function createAccessKey(string $serviceName): string
    {
        $accessKey = self::generateMd5Token($serviceName);

        $this->setSessionProp('accessKey', $accessKey);

        return $accessKey;
    }

    /**
     * Authenticate request using access key
     * 
     * @param   string  Access key from header
     * 
     * @return  bool    Accept or refuse the key
     */
    public function authenticateRequest(string $accessKey): bool
    {
        if (!$this->getSessionProp('serviceName')) {
            throw new Exception('Not specified service name', 403);
        }

        /**
         * @var string | bool Initialized access key from session
         */
        $sessionAccessKey = $this->getSessionProp('accessKey');

        if (!$sessionAccessKey) {
            throw new Exception('Use initialization with service name first', 403);
        }
        
        // strcmp returns 0 if equal
        if (strcmp($accessKey, $sessionAccessKey) === 0) {
            return $this->requestAuthenticated = true;
        }

        return false;
    }

    /**
     * Checks the keys by string compare
     * @param string $serviceName   Service name
     * @param string $serviceKey    Service key got from client
     *
     * @return bool
     */
    private function validateServiceKey(string $serviceName, string $serviceKey): bool
    {
        return strcmp( $serviceKey, $this->services[ $serviceName ] ) === 0;
    }

    /**
     * Authorize service via service key using serviceName from session identified by issued accessKey
     * service name must be specified before via authenticateRequest call
     * Allows privileged actions with API
     * 
     * @param   string  Access key to authenticate the request and serviceName
     * @param   string  Secret service key
     * 
     * @return  bool    Success or fail
     */
    public function authorizeService(string $accessKey, string $serviceKey): ?string
    {
        if (!$this->authenticateRequest($accessKey)) {
            throw new Exception('Not authenticated by access key', 403);
        }

        if (!$serviceKey) {
            throw new Exception('Empty serviceKey', 403);
        }

        $serviceName = $this->getSessionProp('serviceName');
        if (!$serviceName) {
            bdump($serviceName);
            throw new Exception('No service name specified');
        }

        if ($this->validateServiceKey( $serviceName, $serviceKey )) {
            $this->isServiceAuthorized = true;
            $this->setSessionProp('serviceAuthorized', true);
            return $this->createAccessKey($serviceName);
        }

        return null;
    }

    /**
     * Set object default state
     */
    public function closeConnection(): void
    {
        $this->setSessionProp('serviceAuthorized', false);
        $this->setSessionProp('accessKey', false);
        $this->isServiceAuthorized = false;
        $this->isRequestAuthenticated = false;
    }

    /**
     * Get item from request headers
     * 
     * @param string Header to get
     * 
     * @return null|string Requested header data or null
     */
    public static function getHeader(string $headerName): ?string
    {
        // edit header name to find in $_SERVER
        $headerName = 'HTTP_' . \str_replace('-', '_', \strtoupper($headerName));
        return $_SERVER[$headerName] ?? null;
    }

    /**
     * Get current session property
     * 
     * @param string        Property name
     * 
     * @return null|mixed  Data in given session property name
     */
    public function getSessionProp(string $prop)
    {
        return $this->Section->get($prop) ?? null;
    }

    /**
     * Get current session property
     * 
     * @param   string      Property name
     * @param   mixed       Value to save
     * 
     * @return  mixed       Saved value
     */
    public function setSessionProp(string $prop, $value): void
    {
        $this->Section->set($prop, $value);
    }

    /**
     * If request has been authenticated by access key header
     * 
     * @return bool
     */
    public function isRequestAuthenticated(): bool
    {
        return $this->requestAuthenticated;
    }

    /**
     * Get information if current session is authorized using both serviceName and serviceKey and request is authenticated
     * 
     * Allows using private endpoint actions (those with __ prefix)
     * 
     * @return bool Whether current request has verified to API via service key
     */
    public function isServiceAuthorized(): bool
    {
        $serviceAuthorized = $this->getSessionProp('serviceAuthorized');

        return $this->requestAuthenticated && $serviceAuthorized;
    }

    /**
     * Generates Md5 hash token
     * 
     * @return string MD5 token
     */
    public static function generateMd5Token(string $salt): string
    {
        $tokenString = (new \DateTime())->format('Y-m-d-h-i-s') . $salt . Random::generate(16);
        return md5($tokenString);
    }

    /**
     * Checks format of the access key
     * @param string $accessKey
     *
     * @return bool
     */
    public static function isValidAccessKey(string $accessKey)
    {
        return strlen($accessKey) === 32;
    }

    /**
     * Execute the security magic. Run functions should be at the end of the file, so here it is
     */
    public function run(): void
    {
        // check presence of required security headers
        /** @var string */
        $serviceName = self::getHeader(self::HEADER_SERVICE_NAME);
        /** @var string */
        $accessKey = self::getHeader(self::HEADER_ACCESS_KEY);
        // not allowed combination
        if ((!$accessKey || empty($accessKey)) && (!$serviceName || empty($serviceName))) {
            throw new \Exception('Invalid combination of headers');
        }

        /** @var string */
        $serviceKey = self::getHeader(self::HEADER_SERVICE_KEY);
        /** @var string  Current request's querystring */
        $query = $_SERVER['REQUEST_URI'];
        /**
         * @var int Position of endpoint name string in request query
         */
        $position = strpos($query, self::ENDPOINT_NAME);

        // run security section actions only
        if ($position) {
            // action is string starting after the API endpoint name
            $action = substr($query, $position + strlen(self::ENDPOINT_NAME) + 1);

            bdump($accessKey);
            bdump($serviceName);
            bdump($serviceKey);
            bdump($this->isServiceAuthorized());
            bdump($this->isRequestAuthenticated());

            switch ($action) {
                case 'init':
                    if (!$accessKey) {
                        if (!$serviceName) {
                            // need service name to start service
                            throw new Exception('Can\'t identify service', 403);
                        }

                        $res = $this->serviceInit($serviceName);
                        if ($res) {
                            (new CResponse('accessKey', ['accessKey' => $res], 200))->send();
                        } else {
                            throw new Exception("Service initialization failed");
                        }
                    }
                    break;
                case 'authorize':
                    if (!$accessKey || !$serviceKey) {
                        throw new Exception('Can\'t authorize without key', 403);
                    }
                    $res = $this->authorizeService($accessKey, $serviceKey);
                    if ($res) {
                        (new CResponse('accessKey', ['accessKey' => $res], 200))->send();
                    } else {
                        throw new Exception('Service authorization failed, key ' . sprintf('\'%s\' (%s)', $serviceKey, gettype($serviceKey)));
                    }
                    break;
                case 'connectionClose':
                    $this->closeConnection();
                    (new CResponse('closeConnection', null, 200))->send();
                    break;
                default:
                    throw new Exception("Invalid action", 500);
            }
        }

        // or it was specified before and we have HEADER_ACCESS_KEY
        if (!$this->authenticateRequest($accessKey)) {
            throw new Exception('No service name specified', 403);
        }
    }
}