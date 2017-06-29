<?php
declare(strict_types=1);

namespace Firehed\LSP\Message;

trait MessageTrait
{
    private static $EOL = "\r\n";

    abstract protected function getBody();

    public function format()
    {
        $body = $this->getBody();
        $body['jsonrpc'] = '2.0';
        $json = json_encode($body);

        $headers = [
            sprintf('Content-Length: %s', strlen($json)),
            sprintf('Content-Type: %s', 'application/vscode-jsonrpc; charset=utf-8'),
        ];

        $headerLines = implode(self::$EOL, $headers);

        return sprintf('%s%s%s%s',
            $headerLines,
            self::$EOL, self::$EOL,
            $json);
    }

    public function __toString()
    {
        return json_encode($this->getBody());
    }

    public function getMethod()
    {
        if (property_exists($this, 'method')) {
            return $this->method;
        }
        return null;
    }
}
