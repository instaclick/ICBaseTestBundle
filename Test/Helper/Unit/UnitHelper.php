<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper\Unit;

use IC\Bundle\Base\TestBundle\Test\Helper\Helper;

/**
 * Abstract unit helper class.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
abstract class UnitHelper implements Helper
{
    /**
     * @var \IC\Bundle\Base\TestBundle\Test\TestCase
     */
    protected $testCase;

    /**
     * {@inheritdoc}
     */
    public function __construct(\PHPUnit_Framework_TestCase $testCase)
    {
        $this->testCase = $testCase;
    }
}
