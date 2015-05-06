<?php namespace Hook\Database\Relations;

use Illuminate\Database\Eloquent\Relations\HasManyThrough as EloquentHasManyThrough;

class HasManyThrough extends EloquentHasManyThrough
{

    protected function getQualifiedParentKeyName()
    {
        return $this->parent->getTable().'.'.$this->secondKey;
        // return $this->parent->getQualifiedKeyName();
    }

}
