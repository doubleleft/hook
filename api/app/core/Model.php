<?php
namespace Core;

// class User extends \Illuminate\Database\Eloquent\Model
class Model extends \Jenssegers\Mongodb\Model
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


