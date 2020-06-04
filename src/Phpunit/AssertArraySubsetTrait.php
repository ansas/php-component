<?php
declare(strict_types=1);

namespace Ansas\Phpunit;

use Ansas\Phpunit\Constraint\ArraySubset;
use ArrayAccess;
use PHPUnit\Framework\Assert as PhpUnitAssert;
use PHPUnit\Framework\InvalidArgumentException;
use function is_array;

trait AssertArraySubsetTrait
{
    /**
     * Asserts that an array has a specified subset.
     *
     * @param array|ArrayAccess|mixed[] $subset
     * @param array|ArrayAccess|mixed[] $array
     * @param bool                      $strict  [optional]
     * @param string                    $message [optional]
     */
    public static function assertArraySubset($subset, $array, bool $strict = false, string $message = ''): void
    {
        if (!(is_array($subset) || $subset instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                1,
                'array or ArrayAccess'
            );
        }
        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                2,
                'array or ArrayAccess'
            );
        }
        $constraint = new ArraySubset($subset, $strict);
        PhpUnitAssert::assertThat($array, $constraint, $message);
    }
}
