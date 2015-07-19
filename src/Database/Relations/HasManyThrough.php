<?php namespace Hook\Database\Relations;

use Illuminate\Database\Eloquent\Relations\HasManyThrough as EloquentHasManyThrough;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class HasManyThrough extends EloquentHasManyThrough
{

    public function getQualifiedParentKeyName()
    {
        // return $this->parent->getQualifiedKeyName();
        return $this->parent->getTable().'.'.$this->secondKey;
    }

    protected function setJoin(EloquentBuilder $query = null)
    {
        $query = $query ?: $this->query;

        // $foreignKey = $this->related->getTable().'.'.$this->secondKey;
        $foreignKey = $this->related->getQualifiedKeyName();

        $query->join($this->parent->getTable(), $this->getQualifiedParentKeyName(), '=', $foreignKey);
    }

}
