<?php
namespace Hook\Database\Schema\Grammars;
use Illuminate\Database\Schema\Grammars as IlluminateGrammars;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;

class MySqlGrammar extends IlluminateGrammars\MySqlGrammar {

	/**
	 * Create the column definition for a timestamp type.
	 *
	 * @param  \Illuminate\Support\Fluent  $column
	 * @return string
	 */
	protected function typePoint(Fluent $column)
	{
		return 'geometry';
	}

	/**
	 * Compile a plain index key command.
	 *
	 * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
	 * @param  \Illuminate\Support\Fluent  $command
	 * @return string
	 */
	public function compileSpatial(Blueprint $blueprint, Fluent $command)
	{
		return $this->compileKey($blueprint, $command, 'spatial index');
	}

}
