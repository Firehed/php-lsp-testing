<?php
declare(strict_types=1);

namespace Firehed\LSP;

use Psr\Log\LoggerInterface;

class Process
{
    private static $spec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    private $logger;
    private $process;

    private $stdin;
    private $stdout;
    private $stderr;

    private $onRead;

    public function __construct(string $command, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->debug("Starting process");
        $this->process = proc_open($command, self::$spec, $pipes, getcwd(), []);

        if (!is_resource($this->process)) {
            $this->logger->error("Could not open process");
            exit(1);
        }

        list($this->stdin, $this->stdout, $this->stderr) = $pipes;
        $this->logger->debug("Setting pipes to nonblocking");
        stream_set_blocking($this->stdin, false);
        stream_set_blocking($this->stdout, false);
        stream_set_blocking($this->stderr, false);
    }

    public function write(Message\Message $message)
    {
        $format = $message->format();
        $this->logger->debug('>>> ' . $format);
        fwrite($this->stdin, $format);
    }

    public function listen()
    {
        $this->logger->notice('Listening for messages, ^c to exit');
        while (true) {
            $message = $this->readMessageFromStdout();
            if ($message) {
                $this->logger->debug('<<< ' . $message);
                if ($this->onRead) {
                    ($this->onRead)($message);
                }
            };
        }
    }

    private function readMessageFromStdout()
    {
        return $this->readMessage($this->stdout);
    }

    private function readMessage($pipe)
    {
        $buf = '';
        $headers = [];
        while (true) {
            $byte = fread($pipe, 1);
            $read_bytes = strlen($byte);
            if (!$read_bytes) {
                return;
            }
            $buf .= $byte;
            if (substr($buf, -2) == "\r\n") {
                // Catch solo \r\n indicating end of header
                if (strlen($buf) == 2) {
                    break;
                }
                list($header, $value) = explode(': ', $buf);
                $headers[$header] = trim($value);
                $buf = '';
            }
        }
        $len = (int) $headers['Content-Length'] ?? 0;
        $jsonBody = fread($pipe, $len);
        $data = json_decode($jsonBody, true);

        if (array_key_exists('id', $data)) {
            if (array_key_exists('result', $data) || array_key_exists('error', $data)) {
                return new Message\Response($data['id'], $data['result'] ?? null, $data['error'] ?? null);
            }
            if (array_key_exists('method', $data)) {
                return new Message\Request($data['method'], $data['params'] ?? null);
            }
        }
        if (array_key_exists('method', $data)) {
            return new Message\Notification($data['method'], $data['params'] ?? null);
        }

        var_dump($data);
        return;
    }

    public function onRead(callable $callback)
    {
        $this->onRead = $callback;
    }

    public function __destruct()
    {
        proc_close($this->process);
    }
}
