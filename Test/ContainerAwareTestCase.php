<?php
/**
 * @copyright 2013 InstaClick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test case class for container-aware test subjects
 *
 * @author Ryan Albon <ryanalbon@gmail.com>
 */
abstract class ContainerAwareTestCase extends TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->container);

        parent::tearDown();
    }

    /**
     * Retrieve the container
     *
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getContainer()
    {
        return $this->container;
    }
}
