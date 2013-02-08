<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\DataFixtures\DBAL;

use Doctrine\DBAL\Connection;

abstract class AbstractFixtureCreate
{
    public function load(Connection $connection)
    {
        $table   = $this->getTable();
        $columns = $this->getColumns();
        $rows    = $this->getRows();

        $connection->beginTransaction();

        foreach ($rows as $row) {
            $connection->insert($table, array_combine($columns, $row));
        }

        $connection->commit();
    }

    abstract protected function getTable();

    abstract protected function getColumns();

    abstract protected function getRows();
}
