<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

use IC\Bundle\Base\TestBundle\Test\WebTestCase;

/**
 * Abstract helper class.
 *
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 */
abstract class AbstractHelper
{
    /**
     * @var \IC\Bundle\Base\TestBundle\Test\WebTestCase
     */
    protected $testCase;

    /**
     * Define the helper client.
     *
     * @param \IC\Bundle\Base\TestBundle\Test\WebTestCase $testCase
     */
    public function __construct(WebTestCase $testCase)
    {
        $this->testCase = $testCase;
    }
}
