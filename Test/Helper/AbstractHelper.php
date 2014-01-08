<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

use IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase;

/**
 * Abstract helper class.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
abstract class AbstractHelper
{
    /**
     * @var \IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase
     */
    protected $testCase;

    /**
     * Define the helper client.
     *
     * @param \IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase $testCase
     */
    public function __construct(WebTestCase $testCase = null)
    {
        $this->testCase = $testCase;
    }

    public function setTestCase(WebTestCase $testCase)
    {
        $this->testCase = $testCase;
    }
}
