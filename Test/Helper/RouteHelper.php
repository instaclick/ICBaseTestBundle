<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

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
     * If the route is not registered then the test is skipped.
     *
     * @param string $id            Route id
     * @param array  $parameterList Route parameters
     * @param bool   $absolute      Route absolution
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
            $this->testCase->markTestSkipped(sprintf('Failed to acquire route [%s]', $id));
        }
    }
}
