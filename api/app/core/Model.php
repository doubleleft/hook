<?php
namespace Core;

if (\Jenssegers\Mongodb\Model::getConnectionResolver()) {
	class MyModel extends \Jenssegers\Mongodb\Model {}
} else {
	class MyModel extends \Illuminate\Database\Eloquent\Model {}
}

// var_dump(\Illuminate\Database\Eloquent\Model::getConnectionResolver()->hasConnection('mongodb'));

class Model extends MyModel
{

	public function freshTimestamp()
	{
		return time();
	}

	public function getDates()
	{
		return array();
	}

}
