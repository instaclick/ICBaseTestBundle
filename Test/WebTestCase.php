<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test;

use IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase as BaseWebTestCase;

/**
 * Abstract class for Web test cases
 *
 * @deprecated to be removed at a later date
 */
abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * Create a mock object of a given class name.
     *
     * @param string $class Class name
     *
     * @return mixed
     */
    public function getClassMock($class)
    {
        return $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }

}
