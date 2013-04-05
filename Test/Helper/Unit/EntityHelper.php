<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper\Unit;

/**
 * Entity Helper, which helps to create Entity Stubs.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com> 
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
    public function createMock($entityClassName, $id)
    {
        if (isset($this->identityMap[$entityClassName]) && isset($this->identityMap[$entityClassName][$id])){
            return $this->identityMap[$entityClassName][$id];
        }

        $entity = $this->testCase
            ->getMockBuilder($entityClassName)
            ->setMethods(array('getId'))
            ->getMock();
             
        $entity->expects($this->testCase->any())
            ->method('getId')
            ->will($this->testCase->returnValue($id));
        
        $this->identityMap[$entityClassName][$id] = $entity;

        return $entity; 
    }
}
