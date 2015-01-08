<?php namespace Hook\Auth;

use Hook\Model\AuthToken;
use Hook\Application\Config;

class Role {
    protected static $instance;

    protected $builtInRoles = array('all', 'owner');
    protected $defaults = array(
        'crud' => null,
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

    public static function isAllowed($model, $action)
    {
        $instance = static::getInstance();
        $instance->token = AuthToken::current();
        $role = $instance->getConfig($model, 'crud') ?: $instance->getConfig($model, $action);

        if (in_array($role, $instance->builtInRoles)) {
            return call_user_func_array(array($instance, 'check' . ucfirst($role)), array($model));

        } else {
            return $instance->checkRole($role);
        }
    }

    public function getDefaultConfig()
    {
        return $this->defaults;
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

    protected function getConfig($model, $action)
    {
        return Config::get('security.collections.' . $this->getTableName($model) . '.' . $action, $this->defaults[$action]);
    }

    protected function getTableName($model)
    {
        return is_string($model) ? str_plural($model) : $model->getTable();
    }

    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array(array(static::getInstance(), '_'. $method), $arguments);
    }

}
