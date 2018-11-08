<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Monolog\Processor;

use Monolog\Logger;

/**
 * Class ConsoleColorProcessor
 *
 * Adds color to record parts.
 *
 * @package Ansas\Monolog\Processor
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 * @see     http://misc.flogisoft.com/bash/tip_colors_and_formatting
 */
class ConsoleColorProcessor
{
    /**
     * Invoke class.
     *
     * @param  array $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['level_name'] = str_pad($record['level_name'], 9);

        switch ($record['level']) {
            case Logger::DEBUG:
                $record['level_name'] = "\e[36m" . $record['level_name'] . "\e[0m";
                $record['message']    = "\e[2;90m" . $record['message'] . "\e[0m";
                break;

            case Logger::INFO:
                break;

            case Logger::NOTICE:
                $record['level_name'] = "\e[32m" . $record['level_name'] . "\e[0m";
                break;

            case Logger::WARNING:
                $record['level_name'] = "\e[33m" . $record['level_name'] . "\e[0m";
                $record['message']    = "\e[1;43;37m" . $record['message'] . "\e[0m";
                break;

            case Logger::ERROR:
            case Logger::CRITICAL:
            case Logger::ALERT:
            case Logger::EMERGENCY:
                $record['level_name'] = "\e[31m" . $record['level_name'] . "\e[0m";
                $record['message']    = "\e[1;41;97m" . $record['message'] . "\e[0m";
                break;
        }

        return $record;
    }
}
