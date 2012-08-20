<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Loader;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

/**
 * Interface used to subscribe to post load fixture event
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
interface PostLoadSubscriberInterface
{
    public function postLoad(ORMExecutor $executor);
}