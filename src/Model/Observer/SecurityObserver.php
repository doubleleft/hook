<?php namespace Hook\Model\Observer;

use Hook\Auth\Role;
use Hook\Exceptions\NotAllowedException;

class SecurityObserver
{
    protected $trusted = false;
    protected $last_model_instance = null;

    public function toArray($model, $array)
    {
        if (!$this->isAllowed($model, 'read', false)) {
            throw new NotAllowedException();
        }
        return $array;
    }

    public function creating($model)
    {
        if (!$this->isAllowed($model, 'create')) {
            throw new NotAllowedException();
        }
    }

    public function updating($model)
    {
        if (!$this->isAllowed($model, 'update')) {
            throw new NotAllowedException();
        }
    }

    public function updating_multiple($query, $values)
    {
        if (!$this->isAllowed($query->getModel(), 'update')) {
            throw new NotAllowedException();
        }
    }

    public function deleting($model)
    {
        if (!$this->isAllowed($model, 'delete')) {
            throw new NotAllowedException();
        }
    }

    public function deleting_multiple($query)
    {
        if (!$this->isAllowed($query->getModel(), 'delete')) {
            throw new NotAllowedException();
        }
    }

    protected function isAllowed($model, $operation, $new_operation = true) {
        if ($new_operation || !$this->trusted || $this->last_model_instance != $model) {
            $this->trusted = Role::isAllowed($model, $operation);
        }
        $this->last_model_instance = $model;
        return $this->trusted;
    }

}
