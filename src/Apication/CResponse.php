<?php declare(strict_types=1);

namespace APIcation;

use Nette\Utils\Json;

class CResponse
{
    /**
     * @var string Executed command
     */
    private string $action;
    /**
     * @var int Return code
     */
    private int $code;
    /**
     * @var array assoc. array of data
     */
    private array $data;

    public function __construct(
      string $action,
      ?array $data,
      int $code = 0
    ){
        $this->action = $action;
        $this->data = $data;
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getJson(): string
    {
        return Json::encode([
          'action' => $this->action,
          'code'   => $this->code,
          'data'   => $this->data
        ]);
    }

    public function send()
    {
        header('Content-Type: application/json; charset=utf-8');

        http_response_code($this->code);

        die($this->getJson());
    }
}