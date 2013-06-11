<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper\Unit;

/**
 * Entity Helper, which helps to create Entity Stubs.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author John Cartwright <jcartdev@gmail.net>
 */
class EntityHelper extends UnitHelper
{
    /**
     * @var array
     */
    private $identityMap = array();

    /**
     * Create an Entity Mock instance.
     *
     * @param string $entityClassName
     * @param mixed  $id
     */
    public function createMock($entityClassName, $id = null)
    {
        if ($identity = $this->getIdentity($entityClassName, $id)) {
            return $identity;
        }

        $entity = $this->createEntityMock($entityClassName, $id);

        $this->storeIdentity($entityClassName, $id, $entity);

        return $entity;
    }

    /**
     * Create the entity mock
     *
     * @param string $entityClassName
     * @param mixed  $id
     *
     * @return object
     */
    private function createEntityMock($entityClassName, $id)
    {
        $entity = $this->testCase
            ->getMockBuilder($entityClassName)
            ->setMethods(array('getId'))
            ->getMock();

        $entity->expects($this->testCase->any())
            ->method('getId')
            ->will($this->testCase->returnValue($id));

        return $entity;
    }

    /**
     * Check if the id is valid.
     *
     * @param integer $id
     *
     * @return boolean
     */
    private function isIdentifiable($id)
    {
        return $id !== null;
    }

    /**
     * Retrieve the stored reference to the mock object.
     *
     * @param string $entityClassName
     * @param mixed  $id
     *
     * @return mixed
     */
    private function getIdentity($entityClassName, $id)
    {
        if ( ! $this->isIdentifiable($id)) {
            return;
        }

        if ( ! isset($this->identityMap[$entityClassName]) || ! isset($this->identityMap[$entityClassName][$id])) {
            return;
        }

        return $this->identityMap[$entityClassName][$id];
    }

    /**
     * Store the reference to the mock object.
     *
     * @param string $entityClassName
     * @param mixed  $id
     * @param object $entity
     *
     * @return mixed
     */
    private function storeIdentity($entityClassName, $id, $entity)
    {
        if ( ! $this->isIdentifiable($id)) {
            return;
        }

        $this->identityMap[$entityClassName][$id] = $entity;
    }
}
