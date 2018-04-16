<?php
declare(strict_types=1);

namespace Wonderkind\StackdriverLogging;

use Google\Cloud\Logging\PsrLogger;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;

class StackdriverLoggingHandler extends PsrHandler
{
    /**
     * StackdriverLoggingHandler constructor.
     * @param PsrLogger $logger
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(PsrLogger $logger, int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($logger, $level, $bubble);
    }
}
