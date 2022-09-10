<?php
declare(strict_types=1);

namespace Firehed\LSP;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger
{
    public function __construct(string $logLevel)
    {
        $this->level = self::getLevel($logLevel);
    }

    public function log($level, $message, array $context = array())
    {
        if (self::getLevel($level) > $this->level) {
            return;
        }
        $dec = json_decode((string)$message, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $message = json_encode($dec, JSON_PRETTY_PRINT);
        }

        fwrite(STDOUT, $message."\n");
    }

    private static function getLevel(string $level): int
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
                return LOG_EMERG;
            case LogLevel::ALERT:
                return LOG_ALERT;
            case LogLevel::CRITICAL:
                return LOG_CRIT;
            case LogLevel::ERROR:
                return LOG_ERR;
            case LogLevel::WARNING:
                return LOG_WARNING;
            case LogLevel::NOTICE:
                return LOG_NOTICE;
            case LogLevel::INFO:
                return LOG_INFO;
            default:
                return LOG_DEBUG;
        }
    }
}
