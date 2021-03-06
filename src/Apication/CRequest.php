<?php declare(strict_types=1);

namespace APIcation;

use Exception;
use Nette;

/**
 * Represents current Http Request
 */
class CRequest
{
	use Nette\SmartObject;

	/** @var string URI query string */
	private string $queryString;

	/** @var string[] Endpoint name, method, param */
	private array $path = [];

	/** @var string HTTP method */
	private string $method;

	/** @var array */
	private array $post;

	/** @var array */
	private array $files;

	/** @var array */
	private array $headers;

	/** @var array Request was authorized via API key */
	private bool $authorized;

	/**
	 * @param  string  $name  presenter name (module:module:presenter)
	 */
	public function __construct(
		string $queryString,
		string $method,
		array $post,
		array $files,
		array $headers,
		bool $authorized
	) {
		$this->queryString = $queryString;
		$this->method = $method;
		$this->post = $post;
		$this->files = $files;
		$this->headers = $headers;
		$this->authorized = $authorized;

		$this->processPath($queryString);
	}

	public static function breakPath(string $queryString): array
	{
		if (empty($queryString)){
			throw new Exception('Querystring mustnot be empty');
		}
		/**
		 * Path to wanted action
		 * 1. Endpoint
		 * 2. Action
		 */
		$res = explode('/', trim($queryString, '/'));

		// skip API
		if ($res[0] === 'api'){
			array_shift($res);
		}

		return $res;
	}

	private function processPath(string $queryString): void
	{
		$res = self::breakPath($queryString);

		$this->path = [
			$res[0] ? 'E' . ucfirst($res[0]) : null,
			trim($res[1] ?? 'default', "_")
		];
	}

	/**
	 * Get Endpoint name
	 */
	public function getEndpoint(): string
	{
		return $this->path[0];
	}

	/**
	 * Get Endpoint name
	 */
	public function getEndpointPath(): string
	{
		// add full namespace path
		return 'APIcation\Endpoints\\' . $this->path[0];
	}

	/**
	 * Get action name
	 */
	public function getAction(): string
	{
		return $this->path[1];
	}

	/**
	 * Returns a variable provided to the presenter via POST.
	 * If no key is passed, returns the entire array.
	 * @return mixed
	 */
	public function getPost(string $key = null)
	{
		return func_num_args() === 0
			? $this->post
			: ($this->post[$key] ?? null);
	}

	/**
	 * Returns all uploaded files.
	 */
	public function getFiles(): array
	{
		return $this->files;
	}

	/**
	 * Returns current method
	 */
	public function getMethod(): ?string
	{
		return $this->method;
	}

	/**
	 * Get all headers or one of them
	 *
	 * @param string Header name if you want specific one
	 */
	public function getHeader(?string $headerName = null)
	{
		return $headerName ?
			($this->headers[$headerName] ?? false) : $this->headers;
	}

	public function isAuthorized()
	{
		return $this->authorized;
	}

	public function exportBarData(): array
	{
		return [
        $this->getEndpoint(),
        $this->getAction(),
        $this->getEndpointPath(),
        $this->isAuthorized(),
        $this->getPost(),
        $this->headers
      ];
	}
}