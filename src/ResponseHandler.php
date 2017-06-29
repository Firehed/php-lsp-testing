<?php
declare(strict_types=1);

namespace Firehed\LSP;

use Psr\Log\LoggerInterface;

class ResponseHandler
{
    private $log;
    private $process;

    private $gotInitializeResponse = false;

    private $sentDidOpen = false;

    private $version = 1;

    public function __construct(Process $process, LoggerInterface $log)
    {
        $this->process = $process;
        $this->log = $log;
    }

    public function __invoke(Message\Message $message)
    {
        if ($message->getMethod() !== 'window/logMessage') {
            $this->log->info($message);
        }

        if ($message instanceof Message\Response) {
            $result = $message->getResult();
            if ($result && array_key_exists('capabilities', $result)) {
                $this->gotInitializeResponse = true;
            }
        }

        // Send an "opened document" message
        if ($this->gotInitializeResponse && !$this->sentDidOpen) {
            $open = Message\Request::factory('textDocument/didOpen', [
                'textDocument' => [
                    'uri' => realpath('goodfile.php'),
                    'languageId' => 'php',
                    'version' => $this->version++,
                    'text' => file_get_contents('goodfile.php'),
                ],
            ]);
            $this->process->write($open);
            $this->sentDidOpen = true;
            return;
        }


        // Randomly sent a "document changed" message
        if (random_int(1, 50) === 1) {
            $changed = Message\Request::factory('textDocument/didChange', [
                'textDocument' => [
                    'uri' => realpath('goodfile.php'),
                    'version' => $this->version++,
                ],
                'contentChanges' => [
                    [
                        'text' => file_get_contents('goodfile.php'),
                    ],
                ],
            ]);
            $this->process->write($changed);
        }
    }
}
