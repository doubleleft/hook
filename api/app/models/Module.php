<?php
namespace models;

class Module extends \Core\Model
{
	const TYPE_OBSERVER = 'observers';
	const TYPE_ROUTE = 'routes';

	protected $guarded = array();
	protected $primaryKey = '_id';

	public function app() {
		return $this->belongsTo('models\App');
	}

	public function evaluate() {
		$extension = '.' . pathinfo($this->name, PATHINFO_EXTENSION);
		$name = basename($this->name, $extension);
		$klass = ucfirst($name);

		if ($extension === ".php") {
			if ($this->type == self::TYPE_OBSERVER) {
				eval(substr($this->code, 5)); // remove '<?php' for eval

				if (class_exists($klass)) {
					models\Collection::observe(new $klass);
				} else {
					throw new MethodFailureException("Module '{$name}.php' must define a class named '{$klass}'.");
				}

			} else if ($this->type == self::TYPE_ROUTE) {
				$app = \Slim\Slim::getInstance();
				eval(substr($this->code, 5)); // remove '<?php' for eval
			}

		}

	}

}
