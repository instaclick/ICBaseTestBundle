<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

use IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase;

/**
 * Persistence helper class.
 *
 * @author John Cartwright <jcartdev@gmail.com>
 */
class PersistenceHelper extends AbstractHelper
{
    /**
     * @var \Doctrine\Common\DataFixtures\ReferenceRepository
     */
    private $referenceRepository;

    /**
     * {@inheritdoc}
     */
    public function __construct(WebTestCase $testCase)
    {
        parent::__construct($testCase);

        $this->referenceRepository = $testCase->getReferenceRepository();
    }

    /**
     * Transforms reference key to a reference or list of references
     *
     * @param mixed $reference
     *
     * @return mixed
     */
    public function transformToReference($reference)
    {
        if (is_array($reference)) {
            return array_map(array($this->referenceRepository, 'getReference'), $reference);
        }

        return $this->referenceRepository->getReference($reference);
    }
}
