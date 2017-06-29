<?php
declare(strict_types=1);

namespace Firehed\LSP\Message;

class Request implements Message
{
    use MessageTrait;

    private static $autoIncId = 0;

    private $id;
    private $method;
    private $params;

    public function __construct($id, string $method, $params = null)
    {
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
    }

    protected function getBody()
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'params' => $this->params,
        ];
    }

    public static function factory(string $method, $params = null)
    {
        return new Request(self::$autoIncId++, $method, $params);
    }
}
