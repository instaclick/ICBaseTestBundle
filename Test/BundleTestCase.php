<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use IC\Bundle\Base\TestBundle\Test\TestCase;

/**
 * Bundle Unit test case
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
abstract class BundleTestCase extends TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        unset($this->container);

        parent::tearDown();
    }
}