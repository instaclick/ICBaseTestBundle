<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Loader;

use Symfony\Bundle\FrameworkBundle\Client;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 */
class FixtureLoaderFactory
{
    public function getLoader(ContainerInterface $container)
    {
        $managerRegistry = $container->get('doctrine');

        if ($managerRegistry instanceof ManagerRegistry) {
            $objectManager = $registry->getManager($omName);
            $type          = $registry->getName();
        } else {
            $objectManager = $register->getEntityManager();
            $type = 'ORM';
        }

        $loaderFQN = sprintf('\IC\Bundle\Base\TestBundle\Test\Loader\%sFixtureLoader', 
            $type
        );

        if ( ! class_exists($loaderFQN)) {
            throw new \RuntimeException(sprintf(
                'FixtureLoader class "%s" does not exist.',
                $loaderFQN
            ));
        }

        return new $loaderFQN($container);
    }
}
