<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Route helper class.
 *
 * @author John Cartwright <jcartdev@gmail.com>
 */
class RouteHelper extends AbstractHelper
{
    /**
     * Retrieve a generated route from a route id.
     *
     * @param string $id            Route id
     * @param array  $parameterList Route parameters
     *
     * @return mixed
     */
    public function getRoute($id, $parameterList = array(), $absolute = false)
    {
        $client    = $this->testCase->getClient();
        $container = $client->getContainer();
        $router    = $container->get('router');

        try {
            return $router->generate($id, $parameterList, $absolute);
        } catch (RouteNotFoundException $exception) {
            return false;
        }
    }
}
