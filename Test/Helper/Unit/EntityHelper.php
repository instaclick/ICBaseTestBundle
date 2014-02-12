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
     * @param mixed  $readOnlyPropertyList
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function createMock($entityClassName, $readOnlyPropertyList = null)
    {
        $readOnlyPropertyList = $this->normalizePropertyList($entityClassName, $readOnlyPropertyList);
        $identifier            = $this->getIdentifier($readOnlyPropertyList);

        if ($identifier && $identity = $this->getIdentity($entityClassName, $identifier)) {
            return $identity;
        }

        $entity = $this->createEntityMock($entityClassName, $readOnlyPropertyList);

        if ($identifier) {
            $this->storeIdentity($entityClassName, $identifier, $entity);
        }

        return $entity;
    }

    /**
     * Retrieve the identifier.
     *
     * @param mixed $readOnlyPropertyList
     *
     * @return mixed string|false
     */
    private function getIdentifier($readOnlyPropertyList)
    {
        if ( ! isset($readOnlyPropertyList['id'])) {
            return false;
        }

        $value = $readOnlyPropertyList['id']['value'];

        return is_string($value) || is_numeric($value) ? $value : false;
    }

    /**
     * Normalize the property list.
     *
     * @param string $entityClassName
     * @param mixed  $readOnlyPropertyList
     *
     * @return array
     */
    private function normalizePropertyList($entityClassName, $readOnlyPropertyList)
    {
        // For BC, we need to support allowing this property to come in as an identifier
        if (null === $readOnlyPropertyList || ! is_array($readOnlyPropertyList)) {
            $readOnlyPropertyList = array('id' => $readOnlyPropertyList);
        }

        array_walk($readOnlyPropertyList, function (&$value, $key) {
            $value = array(
                'value'     => $value,
                'getMethod' => sprintf('get%s', ucfirst($key)),
            );
        });

        return $readOnlyPropertyList;
    }

    /**
     * Create the entity mock
     *
     * @param string $entityClassName
     * @param array  $readOnlyPropertyMethodList
     *
     * @return object
     */
    private function createEntityMock($entityClassName, $readOnlyPropertyMethodList)
    {
        $methodExtractor = function ($property) {
            return $property['getMethod'];
        };

        $entity = $this->testCase
            ->getMockBuilder($entityClassName)
            ->setMethods(array_map($methodExtractor, $readOnlyPropertyMethodList))
            ->getMock();

        foreach ($readOnlyPropertyMethodList as $property) {
            $entity->expects($this->testCase->any())
                ->method($property['getMethod'])
                ->will($this->testCase->returnValue($property['value']));
        }

        return $entity;
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
        $this->identityMap[$entityClassName][$id] = $entity;
    }
}
