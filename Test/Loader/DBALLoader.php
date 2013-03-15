<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Loader;

use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;

class DBALLoader
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * Constructor.
     *
     * @param \Symfony\Bundle\FrameworkBundle\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function load($managerName = null, array $classList = array())
    {
        $container       = $this->client->getContainer();
        $managerRegistry = $container->get('doctrine');
        $entityManager   = $managerRegistry->getEntityManager($managerName);

        // Preparing fixtures
        $this->prepareFixtureList($entityManager, $classList);
    }

    private function prepareFixtureList(EntityManager $entityManager, array $classList)
    {
        $connection = $entityManager->getConnection();

        sort($classList);

        switch (true) {
            case ($connection->getDriver() instanceof SqliteDriver):
                $this->loadSqliteFixtureList($entityManager, $classList);
                break;

            default:
                break;
        }
    }

    private function loadSqliteFixtureList(EntityManager $entityManager, array $classList)
    {
        $this->entityManager = $entityManager;

        $container      = $this->client->getContainer();
        $connection     = $entityManager->getConnection();

        $parameters     = $connection->getParams();
        $cacheDirectory = $container->getParameter('kernel.cache_dir');
        $database       = isset($parameters['path']) ? $parameters['path'] : $parameters['dbname'];
        $backupDatabase = sprintf('%s/test_populated_%s.db', $cacheDirectory, md5(serialize($classList)));

        // Load fixtures
        if ( ! empty($classList)) {
            $this->execute($classList, true);
        }
    }

    private function execute(array $fixtures, $append = false)
    {
        $executor = $this;
        $this->entityManager->transactional(function(EntityManager $entityManager) use ($executor, $fixtures, $append) {
            if ($append === false) {
                $executor->purge();
            }
            foreach ($fixtures as $fixture) {
                $fixtureInstance = new $fixture();
                $fixtureInstance->load($entityManager->getConnection());
            }
        });
    }
}
