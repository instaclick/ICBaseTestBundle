<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service helper class.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ServiceHelper extends AbstractHelper
{
    /**
     * Retrieve a mock object of a given service name.
     *
     * @param string  $id              Service identifier
     * @param string  $scope           Service scope (default="container")
     * @param boolean $modifyContainer Modify container?
     *
     * @return mixed
     */
    public function mock($id, $scope = ContainerInterface::SCOPE_CONTAINER, $modifyContainer = true)
    {
        $client    = $this->testCase->getClient();
        $container = $client->getContainer();

        $service      = $container->get($id);
        $serviceClass = get_class($service);
        $serviceMock  = $this->testCase->getClassMock($serviceClass);

        if ($modifyContainer) {
            $container->set($id, $serviceMock, $scope);
        }

        return $serviceMock;
    }
}
