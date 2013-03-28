<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

/**
 * Helper interface.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
interface Helper
{
    /**
     * Constructor.
     *
     * @param \PHPUnit_Framework_TestCase $testCase
     */
    public function __construct(\PHPUnit_Framework_TestCase $testCase);
}
