<?php namespace Hook\Auth;

use Hook\Model\AuthToken;

class Role {
    protected static $instance;

    protected $builtInRoles = array('all', 'owner');
    protected $defaults = array(
        'create' => 'all',
        'read' => 'all',
        'update' => 'owner',
        'delete' => 'owner'
    );

    protected $token;

    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function isAllowed($model, $action)
    {
        $this->token = AuthToken::current();
        $role = $this->getRoleConfig($model, 'create');

        if (in_array($role, $this->builtInRoles)) {
            return call_user_func_array(array($this, 'check' . ucfirst($role)), array($model));

        } else {
            return $this->checkRole($role);
        }
    }

    protected function checkAll($model)
    {
        return true;
    }

    protected function checkOwner($model)
    {
        return ($this->token &&
            isset($model['auth_id']) &&
            $model['auth_id'] == $this->token->auth_id);
    }

    protected function checkRole($role) {
        return ($this->token && $this->token->role == $role);
    }

    protected function getRoleConfig($model, $action)
    {
        return Config::get('security.collections.' . $model->getTable() . '.' . $action, $this->defaults[$action]);
    }

    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array(array(static::getInstance(), $method), $arguments);
    }

}
