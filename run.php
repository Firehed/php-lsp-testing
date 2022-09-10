<?php
declare(strict_types=1);

namespace Firehed\LSP;

use Psr\Log\LogLevel;

require_once 'vendor/autoload.php';

$opts = getopt('v');
if (isset($opts['v'])) {
    $level = LogLevel::DEBUG;
} else {
    $level = LogLevel::INFO;
}

$logger = new Logger($level);

// Open subprocess which starts the language server. We will communicate it via
// STDIN and STDOUT
$proc = new Process($_SERVER['_'] . ' ../../slantsearch/vendor/bin/phan --language-server-verbose --language-server-on-stdin', $logger);

// Format and send the initialize message
$init = Message\Request::factory('initialize', [
    'rootPath' => realpath(__DIR__.'/../../slantsearch'),
    'processId' => getmypid(),
    'capabilities' => (object)[],
]);
$proc->write($init);

// Register a callback to handle inbound messages. See
// src/ResponseHandler::__invoke
$handler = new ResponseHandler($proc, $logger);
$proc->onRead($handler);

// Infinite loop, reading out of the subprocess's STDOUT for messages that it
// sends. Any time one is received, it will fire the above callback.
$proc->listen();
