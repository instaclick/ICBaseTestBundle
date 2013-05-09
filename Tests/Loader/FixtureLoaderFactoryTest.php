<?php

namespace IC\Bundle\Base\TestBundle\Test\Loader;

class FixtureLoaderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerInterface'
        );
        $this->managerRegistry = $this->getMockBuilder(
            'Symfony\Bridge\Doctrine\ManagerRegistry'
        )->disableOriginalConstructor()->getMock();
        $this->manager = $this->getMock(
            'Doctrine\Common\Persistence\ObjectManager'
        );
        $this->fixtureLoaderFactory = new FixtureLoaderFactory($this->container);
    }

    public function testGetLoaderWithValidClass()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('doctrine')
            ->will($this->returnValue($this->managerRegistry));
        $this->managerRegistry->expects($this->once())
            ->method('getManager')
            ->with(null)
            ->will($this->returnValue($this->manager));
        $this->managerRegistry->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('ORM'));

        $loader = $this->fixtureLoaderFactory->getLoader($this->container);
        $this->assertInstanceOf(
            '\IC\Bundle\Base\TestBundle\Test\Loader\FixtureLoader\ORMFixtureLoader',
            $loader
        );
    }
}
