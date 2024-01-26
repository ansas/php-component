<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Twig\Extension;

use Ansas\Util\Number;
use Twig_Extension;
use Twig_SimpleFilter;

class Format extends Twig_Extension
{
    /**
     * Returns a list of filters to add to the existing list.
     */
    public function getFilters(): array
    {
        return [
            new Twig_SimpleFilter('readablesize', [Number::class, 'toReadableSize']),
            new Twig_SimpleFilter('readabletime', [Number::class, 'toReadableTime']),
            new Twig_SimpleFilter('readableweight', [Number::class, 'toReadableWeight']),
        ];
    }
}
