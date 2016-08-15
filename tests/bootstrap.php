<?php
/**
 * This file is part of the PHP components package.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

use Composer\Autoload\ClassLoader;

/** @var ClassLoader $autoload */
$autoload = require dirname(__DIR__) . '/vendor/autoload.php';

// Register test classes
$autoload->addPsr4('Ansas\\', __DIR__);
