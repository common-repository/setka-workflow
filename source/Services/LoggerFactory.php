<?php
namespace Setka\Workflow\Services;

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Setka\Workflow\Plugin;

class LoggerFactory
{
    public static function create($name = null)
    {
        if (!$name) {
            $name = Plugin::_NAME_;
        }

        $logger = apply_filters('setka_workflow_logger', $name);

        if (is_a($logger, Logger::class)) {
            return $logger;
        }

        $logger = new Logger($name);

        if (defined('SETKA_WORKFLOW_PHP_UNIT') && true === SETKA_WORKFLOW_PHP_UNIT) {
            $logger->pushHandler(new NullHandler());
            return $logger;
        }

        $logger->pushHandler(new StreamHandler('php://stdout', Logger::ERROR));

        return $logger;
    }
}
