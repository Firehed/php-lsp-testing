<?php
declare(strict_types=1);

namespace Firehed\LSP\Message;

class Notification implements Message
{
    use MessageTrait {
        MessageTrait::__toString as defaultToString;
    }

    private $method;
    private $params;

    public function __construct(string $method, $params = null)
    {
        $this->method = $method;
        $this->params = $params;
    }

    protected function getBody()
    {
        return [
            'method' => $this->method,
            'params' => $this->params,
        ];
    }

    public function __toString()
    {
        switch ($this->method) {
            case 'window/logMessage':
                return sprintf('[%s] %s', $this->params['type'], $this->params['message']);
            default:
                return $this->defaultToString();
        }
    }
}
