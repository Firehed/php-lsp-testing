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
$proc = new Process($_SERVER['_'] . ' vendor/bin/php-language-server.php', $logger);

// Format and send the initialize message
$init = Message\Request::factory('initialize', [
    'rootPath' => getcwd(),
    'processId' => getmypid(),
    'capabilities' => (object)[],
]);
$proc->write($init);

// Register a callback to handle inbound messages. In a more useful context,
// you'd do something more than log them. Possibly respond with a new one
$proc->onRead(function (Message\Message $message) use ($logger, $proc) {
    if ($message->getMethod() !== 'window/logMessage') {
        $logger->info($message);
    }
    // Send a message back? $proc->write($formattedMessage);
});

// Infinite loop, reading out of the subprocess's STDOUT for messages that it
// sends. Any time one is received, it will fire the above callback.
$proc->listen();
