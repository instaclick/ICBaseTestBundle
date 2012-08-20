<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Loader;

use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;

use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as SymfonyFixtureLoader;

use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Data Fixture Loader
 * Implementation innspired by LiipFunctionalTestBundle.
 *
 * @author Juti Noppornpitak <jutin@nationalfibre.net>
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class FixtureLoader
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * @var integer
     */
    private $purgeMode;

    /**
     * Constructor.
     *
     * @param \Symfony\Bundle\FrameworkBundle\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client    = $client;
        $this->purgeMode = ORMPurger::PURGE_MODE_TRUNCATE;
    }

    /**
     * Set the database to the provided fixtures.
     *
     * Refreshes the database and loads fixtures using the specified classes.
     * List of classes is an argument accepting a list of fully qualified class names.
     * These classes must implement Doctrine\Common\DataFixtures\FixtureInterface to be loaded
     * effectively by DataFixtures Loader::addFixture
     *
     * When using SQLite driver, this method will work using 2 levels of cache.
     * - The first cache level will copy the loaded schema, so it can be restored automatically
     * without the overhead of creating the schema for every test case.
     * - The second cache level will copy the schema and fixtures loaded, restoring automatically
     * in the case you are reusing the same fixtures are loaded again.
     *
     * Depends on the doctrine data-fixtures library being available in the class path.
     *
     * @param string $managerName Manager Name
     * @param array  $classList   Class List
     *
     * @return \Doctrine\Common\DataFixtures\Executor\ORMExecutor
     */
    public function load($managerName = null, array $classList = array())
    {
        $container       = $this->client->getContainer();
        $managerRegistry = $container->get('doctrine');
        $entityManager   = $managerRegistry->getEntityManager($managerName);

        // Preparing executor
        $executor = $this->prepareExecutor($entityManager);

        // Preparing fixtures
        $this->prepareFixtureList($executor, $classList);

        return $executor;
    }

    /**
     * Prepare executor
     *
     * @param \Doctrine\ORM\EntityManager   $entityManager
     *
     * @return \Doctrine\Common\DataFixtures\Executor\ORMExecutor
     */
    private function prepareExecutor(EntityManager $entityManager)
    {
        $purger = new ORMPurger($entityManager);

        $purger->setPurgeMode($this->purgeMode);

        $executor   = new ORMExecutor($entityManager, $purger);
        $repository = new ProxyReferenceRepository($entityManager);

        $executor->setReferenceRepository($repository);

        return $executor;
    }

    /**
     * Prepare fixtures
     *
     * @param \Doctrine\Common\DataFixtures\Executor\ORMExecutor $executor  Executor
     * @param array                                              $classList Class List
     */
    private function prepareFixtureList(ORMExecutor $executor, array $classList)
    {
        $connection = $executor->getObjectManager()->getConnection();

        sort($classList);

        switch (true) {
            case ($connection->getDriver() instanceof SqliteDriver):
                $this->loadSqliteFixtureList($executor, $classList);
                break;

            default:
                // Prepare schema
                $schemaHelper = new SchemaLoader($executor->getObjectManager());
                $schemaHelper->load($this->purgeMode);

                // Load fixtures
                $loader = $this->getLoader($classList);

                $this->executePreLoadSubscriberEvent($loader, $executor);

                $executor->execute($loader->getFixtures(), true);

                $this->executePostLoadSubscriberEvent($loader, $executor);
                break;
        }
    }

    /**
     * Executes preload fixture event
     *
     * @param \Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader $loader
     * @param \Doctrine\Common\DataFixtures\Executor\ORMExecutor $executor
     */
    private function executePreLoadSubscriberEvent(SymfonyFixtureLoader $loader, ORMExecutor $executor)
    {
        foreach ($loader->getFixtures() as $fixture) {
            if ( ! $fixture instanceof PreLoadSubscriberInterface) {
                continue;
            }

            $fixture->preLoad($executor);
        }
    }

    /**
     * Executes postload fixture event
     *
     * @param \Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader $loader
     * @param \Doctrine\Common\DataFixtures\Executor\ORMExecutor $executor
     */
    private function executePostLoadSubscriberEvent(SymfonyFixtureLoader $loader, ORMExecutor $executor)
    {
        foreach ($loader->getFixtures() as $fixture) {
            if ( ! $fixture instanceof PostLoadSubscriberInterface) {
                continue;
            }

            $fixture->postLoad($executor);
        }
    }

    /**
     * Load SQLite data fixture list
     *
     * @param \Doctrine\Common\DataFixtures\Executor\ORMExecutor $executor  Executor
     * @param array                                              $classList Class List
     */
    private function loadSqliteFixtureList(ORMExecutor $executor, array $classList)
    {
        $container      = $this->client->getContainer();
        $entityManager  = $executor->getObjectManager();
        $connection     = $entityManager->getConnection();
        $loader         = $this->getLoader($classList);

        $parameters     = $connection->getParams();
        $cacheDirectory = $container->getParameter('kernel.cache_dir');
        $database       = isset($parameters['path']) ? $parameters['path'] : $parameters['dbname'];
        $backupDatabase = sprintf('%s/test_populated_%s.db', $cacheDirectory, md5(serialize($classList)));

        $this->executePreLoadSubscriberEvent($loader, $executor);

        if (file_exists($backupDatabase)) {
            $executor->getReferenceRepository()->load($backupDatabase);

            copy($backupDatabase, $database);

            $this->executePostLoadSubscriberEvent($loader, $executor);
            return;
        }

        // Prepare schema
        $schemaHelper = new SchemaLoader($entityManager);
        $schemaHelper->setCacheDirectory($cacheDirectory);
        $schemaHelper->load($this->purgeMode);

        // Load fixtures
        if ( ! empty($classList)) {
            $executor->execute($loader->getFixtures(), true);
            $executor->getReferenceRepository()->save($backupDatabase);

            copy($database, $backupDatabase);
        }

        $this->executePostLoadSubscriberEvent($loader, $executor);
    }

    /**
     * Retrieve Doctrine DataFixtures loader.
     *
     * @param array $classList Class List
     *
     * @return \Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader
     */
    private function getLoader(array $classList)
    {
        $container = $this->client->getContainer();
        $loader    = new SymfonyFixtureLoader($container);

        foreach ($classList as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        return $loader;
    }

    /**
     * Load a data fixture class.
     *
     * @param \Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader $loader    Loader
     * @param string                                                     $className Class Name
     */
    private function loadFixtureClass(SymfonyFixtureLoader $loader, $className)
    {
        $fixture = new $className();

        if ($loader->hasFixture($fixture)) {
            return;
        }

        $loader->addFixture($fixture);

        if ( ! $fixture instanceof DependentFixtureInterface) {
            return;
        }

        foreach ($fixture->getDependencies() as $dependency) {
            $this->loadFixtureClass($loader, $dependency);
        }
    }
}


