<?php
declare(strict_types=1);
namespace Firehed\LSP;

require_once 'vendor/autoload.php';

$logger = new Logger(\Psr\Log\LogLevel::INFO);
$proc = new Process($_SERVER['_'] . ' vendor/bin/php-language-server.php', $logger);

$init = Message\Request::factory('initialize', [
    'rootPath' => getcwd(),
    'processId' => getmypid(),
    'capabilities' => (object)[],
]);

$proc->write($init);

$proc->onRead(function(Message\Message $message) use ($logger) {
    if ($message->getMethod() !== 'window/logMessage') {
        $logger->info($message);
    }
});

$proc->listen();
