<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Loader;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This factory returns the fixture loader which
 * corresponds to the current ManagerRegistry type
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class FixtureLoaderFactory
{
    public function getLoader(ContainerInterface $container, $managerName = null)
    {
        $managerRegistry = $container->get('doctrine');

        if ($managerRegistry instanceof ManagerRegistry) {
            $objectManager = $managerRegistry->getManager($managerName);
            $type          = $managerRegistry->getName();
        } else {
            $objectManager = $managerRegistry->getEntityManager();
            $type = 'ORM';
        }

        $loaderFQN = sprintf(
            '\IC\Bundle\Base\TestBundle\Test\Loader\FixtureLoader\%sFixtureLoader', 
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
