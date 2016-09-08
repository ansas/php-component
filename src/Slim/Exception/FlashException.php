<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Exception;

use Exception;

class FlashException extends Exception
{
    /**
     * Flash message key
     */
    const ERROR = 'error';

    /**
     * Flash message key
     */
    const WARNING = 'warning';

    /**
     * Flash message key
     */
    const INFO = 'info';

    /**
     * Flash message key
     */
    const SUCCESS = 'success';

    /**
     * @var string Flash key
     */
    protected $key;

    /**
     * Create new exception
     *
     * @param string $message
     * @param string $key [optional]
     */
    public function __construct(string $message, string $key = self::ERROR)
    {
        parent::__construct($message);
        $this->key = $key;
    }

    /**
     * Get key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}
