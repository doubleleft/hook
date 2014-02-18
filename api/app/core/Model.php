<?php
namespace Core;

class Model extends \DLModel
{

	public function freshTimestamp()
	{
		return time();
	}

	public function freshTimestampString()
	{
		return time();
	}

	public function getDates()
	{
		return array();
	}

}
