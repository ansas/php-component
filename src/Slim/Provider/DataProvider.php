<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Provider;

use Ansas\Component\Collection\Collection;
use Pimple\Container;

/**
 * Class DataProvider
 *
 * @package App\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class DataProvider extends AbstractProvider
{
    /**
     * @var null|string[] Collections to register.
     */
    protected $collections;

    /**
     * DataProvider constructor.
     *
     * @param null|string|string[] $collections [optional] Collections to register.
     */
    public function __construct($collections = null)
    {
        $this->setCollections($collections);
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Set 'data' as default collection container that will always be created and add individual collections
        $collections = ['data'];
        if ($this->collections) {
            $collections = array_merge($collections, $this->collections);
            $collections = array_unique($collections);
        }

        foreach ($collections as $collection) {
            /**
             * Add dependency (DI).
             *
             * @return Collection
             */
            $container[$collection] = function () {
                return new Collection();
            };
        }
    }

    /**
     * Set collections to create on container registration.
     *
     * @param null|string|string[] $collections [optional] Collections to register.
     *
     * @return $this
     */
    public function setCollections($collections)
    {
        $this->collections = $collections ? (array) $collections : null;

        return $this;
    }
}
