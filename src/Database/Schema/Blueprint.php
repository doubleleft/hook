<?php namespace Hook\Database\Schema;

use Illuminate\Database\Schema as IlluminateSchema;

class Blueprint extends IlluminateSchema\Blueprint
{

    /**
     * newColumn - alias to addColumn, which is protected
     *
     * @see addColumn
     */
    public function newColumn($type, $name, array $parameters = array())
    {
        return $this->addColumn($type, $name, $parameters);
    }

    /**
     * newColumn - alias to addColumn, which is protected
     *
     * @see addColumn
     */
    public function point($name, array $parameters = array())
    {
        return $this->addColumn('point', $name, $parameters);
    }

    /**
     * Specify a unique index for the table.
     *
     * @param  string|array  $columns
     * @param  string  $name
     * @return \Illuminate\Support\Fluent
     */
    public function spatialIndex($columns, $name = null)
    {
        $connection_klass = get_class(\DLModel::getConnectionResolver()->connection());
        if ($connection_klass == 'Illuminate\Database\MySqlConnection') {
            // MySQL: Only MyISAM engine have spatial indexing
            $this->engine = 'MyISAM';
        }

        return $this->indexCommand('spatial', $columns, $name);
    }

}
