<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture as DoctrineAbstractFixture;

/**
 * Abstract Fixture Update
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
abstract class AbstractFixtureUpdate extends DoctrineAbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getDataList() as $referenceKey => $data) {

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
     * @param array $data
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