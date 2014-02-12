<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Abstract Fixture Update
 *
 * @author John Cartwright <jcartdev@gmail.com>
 */
abstract class AbstractFixtureUpdate extends AbstractFixture
{
    /**
     * Updates and persists the entities provided from the data list
     *
     * @param Doctrine\Common\Persistence\ObjectManager $manager
     * 
     * @throws \InvalidArgumentException If invalid reference key to update was provided
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getDataList() as $referenceKey => $data) {
            if ( ! $this->hasReference($referenceKey)) {
                throw new \InvalidArgumentException('Reference ['. $referenceKey .'] does not exist');
            }

            $entity = $this->updateEntity($data, $this->getReference($referenceKey));

            if ( ! $entity) {
                continue;
            }

            $manager->persist($entity);

            $this->setReference($referenceKey, $entity);
        }

        $manager->flush();
    }

    /**
     * Update an entity
     *
     * @param array  $data
     * @param object $entity
     *
     * @return object
     */
    abstract protected function updateEntity($data, $entity);

    /**
     * Retrieve the data list of entities
     *
     * @return array
     */
    abstract protected function getDataList();
}
