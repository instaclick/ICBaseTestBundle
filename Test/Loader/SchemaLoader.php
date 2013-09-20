<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Loader;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\DBAL\Driver\PDOMySql\Driver as MySqlDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Schema Loader
 * Implementation innspired by LiipFunctionalTestBundle.
 *
 * @author Juti Noppornpitak <jnopporn@shiroyuki.com>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class SchemaLoader
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * Constructor.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieve the associated EntityManager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Retrieve the cache directory.
     *
     * @return string
     */
    public function getCacheDirectory()
    {
        return $this->cacheDirectory;
    }

    /**
     * Define the cache directory.
     *
     * @param string $cacheDirectory
     */
    public function setCacheDirectory($cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * Load the Database Schema.
     *
     * @param integer $purgeMode
     *
     * @TODO: Remove the FK checks when Doctrine comes up with a solution for truncating tables with FK constraints
     */
    public function load($purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        $connection = $this->entityManager->getConnection();

        switch (true) {
            case ($connection->getDriver() instanceof SqliteDriver):
                $this->loadSqliteSchema();
                break;
            default:
                $purger = new ORMPurger($this->entityManager);
                $purger->setPurgeMode($purgeMode);

                $executor = new ORMExecutor($this->entityManager, $purger);
                $executor->setReferenceRepository(new ReferenceRepository($this->entityManager));

                // Temporary fix for making fixtures work with MySQL versions >= 5.5
                if ($connection->getDriver() instanceof MySqlDriver) {
                    // Get original value to ba able to reset to original state
                    $config = $connection
                        ->executeQuery("SHOW VARIABLES LIKE 'foreign_key_checks'")
                        ->fetchAll(\PDO::FETCH_KEY_PAIR)
                    ;

                    // We assume ON if the setting can't somehow be retrieved,
                    // this should only happen when db connection breaks, so we go safe way here
                    $configValue = (isset($config['foreign_key_checks'])) ? $config['foreign_key_checks'] : 'ON';

                    if (in_array($configValue, array('ON', '1'), true)) {
                        $connection->exec('SET foreign_key_checks=OFF');
                    }
                }

                $executor->purge();

                // Resetting the FK checks to original state
                if ($connection->getDriver() instanceof MySqlDriver && isset($configValue)) {
                    $connection->exec(sprintf('SET foreign_key_checks=%s', $configValue));
                }

                break;
        }
    }

    /**
     * Load SQLite Driver Schema.
     */
    private function loadSqliteSchema()
    {
        $connection   = $this->entityManager->getConnection();
        $metadataList = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $parameters     = $connection->getParams();
        $database       = isset($parameters['path']) ? $parameters['path'] : $parameters['dbname'];
        $backupDatabase = sprintf('%s/test_%s.db', $this->cacheDirectory, md5(serialize($metadataList)));

        if ( ! (null !== $this->cacheDirectory && file_exists($backupDatabase))) {
            $schemaTool = new SchemaTool($this->entityManager);

            $schemaTool->dropDatabase($database);
            $schemaTool->createSchema($metadataList);

            // Flip the database saving process. The actual one as the primary
            $tmpDatabase    = $database;
            $database       = $backupDatabase;
            $backupDatabase = $tmpDatabase;
        }

        // Only cache if cache directory is configured
        if (null !== $this->cacheDirectory) {
            copy($backupDatabase, $database);
        }
    }
}
