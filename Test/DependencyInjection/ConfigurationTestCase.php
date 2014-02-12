<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use IC\Bundle\Base\TestBundle\Test\TestCase;

/**
 * Dependency Injection Configuration Unit test case.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
abstract class ConfigurationTestCase extends TestCase
{
    /**
     * @var \Symfony\Component\Config\Definition\Processor
     */
    protected $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->processor = new Processor();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->processor);

        parent::tearDown();
    }

    /**
     * Processes an array of raw configuration and returns a compiled version.
     *
     * @param \Symfony\Component\Config\Definition\ConfigurationInterface $configuration
     * @param array                                                       $rawConfiguration
     *
     * @return array A normalized array
     */
    public function processConfiguration(ConfigurationInterface $configuration, array $rawConfiguration)
    {
        return $this->processor->processConfiguration($configuration, $rawConfiguration);
    }
}
