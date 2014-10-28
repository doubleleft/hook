<?php namespace Hook\Session\Handlers;

use SessionHandlerInterface;

// heavily based on Illumimnate\Session:
// https://github.com/illuminate/session/blob/master/DatabaseSessionHandler.php

class Database implements SessionHandlerInterface {

    protected $connection;
    protected $table = '__sessions';

    protected $exists;

    public function __construct() {
        $this->connection = \DLModel::getConnectionResolver()->connection();
    }

    public function read($session_id) {
        $session = (object) $this->getQuery()->find($session_id);

        if (isset($session->payload)) {
            $this->exists = true;
            return base64_decode($session->payload);
        }
    }

    public function write($session_id, $data) {
        if ($this->exists) {
            $this->getQuery()->where('id', $session_id)->update(array(
                'payload' => base64_encode($data),
                'last_activity' => time(),
            ));
        } else {
            $this->getQuery()->insert(array(
                'id' => $session_id,
                'payload' => base64_encode($data),
                'last_activity' => time(),
            ));
        }

        $this->exists = true;
    }

    public function destroy($session_id) {
        $this->getQuery()->where('id', $session_id)->delete();
    }

    public function gc($lifetime) {
        $this->getQuery()->where('last_activity', '<=', time() - $lifetime)->delete();
    }

    public function open($save_path, $name) {
        return true;
    }

    public function close() {
        return true;
    }

    protected function getQuery() {
        return $this->connection->table($this->table);
    }

}
