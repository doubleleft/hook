<?php namespace API\Database\Schema;

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

}
