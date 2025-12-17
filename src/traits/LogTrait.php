<?php

declare(strict_types=1);
/**
 * Secret Santa Plugin for Craft CMS 5.x
 *
 * Secret Santa Plugin
 *
 * @link      https://www.marukka.ch/plugins
 * @copyright Copyright (c) 2025 Mischa Sprecher
 */

namespace nibiru\secretsanta\traits;

use Craft;
use yii\log\Logger;

trait LogTrait
{
    // Static Methods
    // =========================================================================

    public static function info(string $message, array $params = []): void
    {
        self::_log(Logger::LEVEL_INFO, $message, $params);
    }

    public static function warning(string $message, array $params = []): void
    {
        self::_log(Logger::LEVEL_WARNING, $message, $params);
    }

    public static function error(string $message, array $params = []): void
    {
        self::_log(Logger::LEVEL_ERROR, $message, $params);
    }


    // Private Methods
    // =========================================================================

    private static function _log(int|string $level, string $message, array $params = []): void
    {
        if ($params && self::getInstance()) {
            $message = Craft::t(self::getInstance()->handle, $message, $params);
        }

        // Send everything under the plugins category
        Craft::getLogger()->log($message, $level, 'secret-santa');
    }
}
