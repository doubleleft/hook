<?php namespace Hook\Model\Observer;

use Hook\Auth\Role;
use Hook\Exceptions\NotAllowedException;

class SecurityObserver
{

    public function toArray($model, $array)
    {
        if (!Role::isAllowed($model, 'read')) {
            throw new NotAllowedException();
        }

        return $array;
    }

    public function creating($model)
    {
        if (!Role::isAllowed($model, 'create')) {
            throw new NotAllowedException();
        }
    }

    public function updating($model)
    {
        if (!Role::isAllowed($model, 'update')) {
            throw new NotAllowedException();
        }
    }

    public function updating_multiple($query, $values)
    {
        if (!Role::isAllowed($model, 'update')) {
            throw new NotAllowedException();
        }
    }

    public function deleting($model)
    {
        if (!Role::isAllowed($model, 'delete')) {
            throw new NotAllowedException();
        }
    }

    public function deleting_multiple($query)
    {
        if (!Role::isAllowed($model, 'delete')) {
            throw new NotAllowedException();
        }
    }

}
