<?php
namespace API\Database;

use API\Model\App as App;

/**
 * AppContext
 * @author Endel Dreyer <edreyer@doubleleft.com>
 */
class AppContext
{

    public static function setPrefix($prefix) {
        $connection = \DLModel::getConnectionResolver()->connection();
        $connection->setTablePrefix($connection->getTablePrefix() . 'app' . $prefix . '_');
    }

    /**
     * getPrefix
     *
     * @return string
     */
    public static function getPrefix() {
        $connection = \DLModel::getConnectionResolver()->connection();
        return $connection->getTablePrefix();
    }

    /**
     * migrate
     *
     * Migrate core application schema.
     */
    public static function migrate() {
        $connection = \DLModel::getConnectionResolver()->connection();
        if ($connection->getPdo()) {
            $builder = $connection->getSchemaBuilder();
            if (!$builder->hasTable('modules')) {
                foreach (glob(__DIR__ . '/../../migrations/app/*.php') as $file) {
                    $migration = require($file);
                    $builder->create($connection->getTablePrefix() . key($migration), current($migration));
                }
            }
        }
    }

}
