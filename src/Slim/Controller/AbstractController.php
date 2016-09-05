<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Controller;

use Ansas\Component\Collection\Collection;
use Ansas\Monolog\Profiler;
use Ansas\Slim\Handler\ContainerInjectTrait;
use Ansas\Slim\Handler\FlashHandler;
use Ansas\Slim\Handler\TwigHandlerTrait;
use Monolog\Logger;
use PDO;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;
use Slim\Views\Twig;

/**
 * Class AbstractController
 *
 * @package Ansas\Slim\Controller
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property Collection   $data
 * @property FlashHandler $flash
 * @property Logger       $logger
 * @property PDO          $pdo
 * @property Profiler     $profiler
 * @property Router       $router
 * @property array        $settings
 * @property Twig         $view
 */
abstract class AbstractController
{
    use ContainerInjectTrait;
    use TwigHandlerTrait;

    /**
     * Not found.
     *
     * @param  Request  $request
     * @param  Response $response
     *
     * @return Response
     */
    public function notFound(Request $request, Response $response)
    {
        $handler = $this->container->get('notFoundHandler');

        return $handler($request, $response);
    }

}
