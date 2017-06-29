<?php
declare(strict_types=1);

namespace Firehed\LSP\Message;

interface Message
{
    public function __toString();

    public function format();

    public function getMethod();
}
