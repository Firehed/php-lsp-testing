<?php
declare(strict_types=1);

namespace Firehed\LSP\Message;

class Response implements Message
{
    use MessageTrait;

    private $id;
    private $result;
    private $error;

    public function __construct($id, $result, $error)
    {
        $this->id = $id;
        $this->result = $result;
        $this->error = $error;
    }

    protected function getBody()
    {
        return [
            'id' => $this->id,
            'result' => $this->result,
            'error' => $this->error,
        ];
    }
}
