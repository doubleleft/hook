<?php namespace Hook\Session\Handlers;

// By Unirgy
// http://stackoverflow.com/questions/1724587/how-to-store-php-sessions-in-apc-cache
class Memory
{
    protected $_prefix;
    protected $_ttl;
    protected $_lockTimeout = 10; // if empty, no session locking, otherwise seconds to lock timeout

    public function __construct($params=array())
    {
        $def = session_get_cookie_params();
        $this->_ttl = $def['lifetime'];
        if (isset($params['ttl'])) {
            $this->_ttl = $params['ttl'];
        }
        if (isset($params['lock_timeout'])) {
            $this->_lockTimeout = $params['lock_timeout'];
        }

        session_set_save_handler(
            array($this, 'open'), array($this, 'close'),
            array($this, 'read'), array($this, 'write'),
            array($this, 'destroy'), array($this, 'gc')
        );
    }

    public function open($savePath, $sessionName)
    {
        $this->_prefix = 'BSession/'.$sessionName;
        if (!apc_exists($this->_prefix.'/TS')) {
            // creating non-empty array @see http://us.php.net/manual/en/function.apc-store.php#107359
            apc_store($this->_prefix.'/TS', array(''));
            apc_store($this->_prefix.'/LOCK', array(''));
        }
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $key = $this->_prefix.'/'.$id;
        if (!apc_exists($key)) {
            return ''; // no session
        }

        // redundant check for ttl before read
        if ($this->_ttl) {
            $ts = apc_fetch($this->_prefix.'/TS');
            if (empty($ts[$id])) {
                return ''; // no session
            } elseif (!empty($ts[$id]) && $ts[$id] + $this->_ttl < time()) {
                unset($ts[$id]);
                apc_delete($key);
                apc_store($this->_prefix.'/TS', $ts);
                return ''; // session expired
            }
        }

        if (!$this->_lockTimeout) {
            $locks = apc_fetch($this->_prefix.'/LOCK');
            if (!empty($locks[$id])) {
                while (!empty($locks[$id]) && $locks[$id] + $this->_lockTimeout >= time()) {
                    usleep(10000); // sleep 10ms
                    $locks = apc_fetch($this->_prefix.'/LOCK');
                }
            }
            /*
            // by default will overwrite session after lock expired to allow smooth site function
            // alternative handling is to abort current process
            if (!empty($locks[$id])) {
                return false; // abort read of waiting for lock timed out
            }
            */
            $locks[$id] = time(); // set session lock
            apc_store($this->_prefix.'/LOCK', $locks);
        }

        return apc_fetch($key); // if no data returns empty string per doc
    }

    public function write($id, $data)
    {
        $ts = apc_fetch($this->_prefix.'/TS');
        $ts[$id] = time();
        apc_store($this->_prefix.'/TS', $ts);

        $locks = apc_fetch($this->_prefix.'/LOCK');
        unset($locks[$id]);
        apc_store($this->_prefix.'/LOCK', $locks);

        return apc_store($this->_prefix.'/'.$id, $data, $this->_ttl);
    }

    public function destroy($id)
    {
        $ts = apc_fetch($this->_prefix.'/TS');
        unset($ts[$id]);
        apc_store($this->_prefix.'/TS', $ts);

        $locks = apc_fetch($this->_prefix.'/LOCK');
        unset($locks[$id]);
        apc_store($this->_prefix.'/LOCK', $locks);

        return apc_delete($this->_prefix.'/'.$id);
    }

    public function gc($lifetime)
    {
        if ($this->_ttl) {
            $lifetime = min($lifetime, $this->_ttl);
        }
        $ts = apc_fetch($this->_prefix.'/TS');
        foreach ($ts as $id=>$time) {
            if ($time + $lifetime < time()) {
                apc_delete($this->_prefix.'/'.$id);
                unset($ts[$id]);
            }
        }
        return apc_store($this->_prefix.'/TS', $ts);
    }
}
