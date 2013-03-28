<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Loader;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

/**
 * Interface used to subscribe to post load fixture event
 *
 * @author John Cartwright <jcartdev@gmail.com>
 */
interface PostLoadSubscriberInterface
{
    /**
     * Post load fixture event listener
     *
     * @param \Doctrine\Common\DataFixtures\Executor\ORMExecutor $executor
     */
    public function postLoad(ORMExecutor $executor);
}
