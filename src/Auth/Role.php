<?php namespace Hook\Auth;

use Hook\Model\AppKey;
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
        // commandline always have full-access
        if (AppKey::current()->isCommandline()) {
            return true;
        }

        $instance = static::getInstance();
        $collection_name = $instance->getCollectioName($model);

        $instance->token = AuthToken::current();
        $role = $instance->getConfig($collection_name, $action);

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
        $auth_id_field = ($this->getCollectioName($model) == 'auths') ? '_id' : 'auth_id';
        return ($this->token &&
            isset($model[$auth_id_field]) &&
            $model[$auth_id_field] == $this->token->auth_id);
    }

    protected function checkRole($role) {
        return ($this->token && $this->token->role == $role);
    }

    protected function getConfig($collection_name, $action)
    {
        $role = null;

        $security = Config::get('security.collections.' . $collection_name, array());
        if (isset($security[$action])) {
            $role = $security[$action];

        } else if (isset($security['crud'])) {
            $role = $security['crud'];

        } else {
            $role = $this->defaults[$action];
        }

        return $role ?: "all";
    }

    protected function getCollectioName($model)
    {
        return is_string($model) ? str_plural($model) : $model->getTable();
    }

    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array(array(static::getInstance(), '_'. $method), $arguments);
    }

}
