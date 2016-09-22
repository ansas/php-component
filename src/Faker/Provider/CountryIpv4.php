<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Faker\Provider;

use Faker\Provider\Base;

/**
 * Class CountryIpv4
 *
 * @package Ansas\Faker\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CountryIpv4 extends Base
{

    /**
     * @var array Ipv4 ranges for countries (must have last part 1..254 available in block)
     */
    protected static $ipv4Ranges = [
        'DE' => [
            '204.79.177.',
            '195.190.145.',
            '62.113.195.',
            '86.106.120.',
            '92.253.208.',
            '185.71.33.',
            '213.164.67.',
        ],
    ];

    /**
     * Generate an IPv4 for country
     *
     * @param string $country
     *
     * @return string
     */
    protected function ipv4For($country)
    {
        $country = self::toUpper($country);

        if (!isset(self::$ipv4Ranges[$country])) {
            return '';
        }

        return static::randomElement(self::$ipv4Ranges[$country]) . mt_rand(1, 254);
    }

    /**
     * Generate an IPv4 for Germany (DE)
     *
     * @return string
     */
    public function ipv4ForDe()
    {
        return $this->ipv4For('DE');
    }

}
