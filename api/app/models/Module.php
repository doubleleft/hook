<?php
namespace models;

class Module extends \Core\Model
{
	const TYPE_TEMPLATE = 'templates';
	const TYPE_OBSERVER = 'observers';
	const TYPE_ROUTE = 'routes';

	protected $guarded = array();
	protected $primaryKey = '_id';

	public function app() {
		return $this->belongsTo('models\App');
	}

	/**
	 * Get a route module instance
	 * @param string name
	 */
	public static function route($name) {
		return static::get(self::TYPE_ROUTE, $name.'.php');
	}

	/**
	 * Get a observer module instance
	 * @param string name
	 */
	public static function observer($name) {
		return static::get(self::TYPE_OBSERVER, $name.'.php');
	}

	/**
	 * Get a template module instance, trying to fallback to a general template
	 * @param string name
	 */
	public static function template($name) {
		$template = null;

		//
		// It's a template name or template contents?
		//
		if (preg_match('/^[A-Za-z0-9_\.\-\/]+\.html$/', $name)) {
			// It's a template name!
			$template = static::get(self::TYPE_TEMPLATE, $name);

			if (!$template) {
				$fallback_template_path = __DIR__ . '/../storage/default/templates/' . $name;
				// try to retrieve local fallback template
				if (!file_exists($fallback_template_path)) {
					throw new \MethodFailureException("Template not found: '{$name}'. Please run `dl-api generate:template {$name}` to generate one.");
				}

				// use local template if can't find
				$template = new static(array(
					'type' => self::TYPE_TEMPLATE,
					'code' => file_get_contents($fallback_template_path),
					'name' => $name
				));
			}
		}

		if (!$template) {
			$template = new static(array(
				'type' => self::TYPE_TEMPLATE,
				'code' => $name,
				'name' => 'inline-template.html'
			));
		}

		return $template;
	}

	/**
	 * Get a module instance
	 * @param string type
	 * @param string name
	 */
	public static function get($type, $name) {
		return static::currentApp()->where('type', $type)->where('name', $name)->first();
	}

	/**
	 * Compile module code
	 */
	public function compile($options=array()) {
		$extension = '.' . pathinfo($this->name, PATHINFO_EXTENSION);
		$name = basename($this->name, $extension);

		if ($extension === ".php") {
			//
			// Expose handy aliases for modules
			//
			$aliases = "use models\App as App;\n";
			$aliases.= "use models\Collection as Collection;\n";


			if ($this->type == self::TYPE_OBSERVER) {
				eval($aliases . substr($this->code, 5)); // remove '<?php' for eval
				$klass = ucfirst($name);

				if (class_exists($klass)) {
					Collection::observe(new $klass);
				} else {
					throw new \MethodFailureException("Module '{$name}.php' must define a class named '{$klass}'.");
				}

			} else if ($this->type == self::TYPE_ROUTE) {
				$app = \Slim\Slim::getInstance();
				eval($aliases . substr($this->code, 5)); // remove '<?php' for eval
			}

		} else if ($extension === '.html') {
			$template = $this->code;

			// always use array for options
			if (gettype($options)==='object') {
				$options = $options->toArray();
			}

			foreach($options as $field => $value) {
				//
				// Please consider migrating it to mustache, for more complex templates:
				// https://github.com/bobthecow/mustache.php
				//
				if (gettype($value)==="object") { continue; }
				$template = preg_replace('/{{'.$field.'}}/', $value, $template);
			}

			return $template;
		}

	}

	/**
	 * Current app scope
	 * @example
	 *     AppConfig::current()->where('name', 'like', 'mail.%')->get()
	 */
	public function scopeCurrentApp($query) {
		$app = \Slim\Slim::getInstance();
		return $query->where('app_id', $app->key->app_id);
	}
}
