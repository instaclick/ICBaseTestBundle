<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Abstract Fixture Create
 *
 * @author John Cartwright <jcartdev@gmail.com>
 */
abstract class AbstractFixtureCreate extends AbstractFixture
{
    /**
     * Creates and persists the entities provided from the data list
     *
     * @param Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getDataList() as $referenceKey => $data) {
            if ($this->hasReference($referenceKey)) {
                continue;
            }

            $entity = $this->buildEntity($data);

            if ( ! $entity) {
                continue;
            }

            $manager->persist($entity);

            if (is_int($referenceKey)) {
                continue;
            }

            $this->setReference($referenceKey, $entity);
        }

        $manager->flush();
    }

    /**
     * Build an entity
     *
     * @param array $data
     *
     * @return \IC\Bundle\Base\ComponentBundle\Entity
     */
    abstract protected function buildEntity($data);

    /**
     * Retrieve the data list of entities
     *
     * @return array
     */
    abstract protected function getDataList();
}
