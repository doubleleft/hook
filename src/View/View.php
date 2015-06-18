<?php namespace Hook\View;

use Hook\Http\Router;
use Hook\Exceptions\NotFoundException;

use LightnCandy;
use LCRun3;

use Hook\Model\Module;

use SplStack;

class View extends \Slim\View
{
    /**
     * helpers
     *
     * @var \Slim\Helper\Set
     */
    public $helpers;

    /**
     * block_helpers
     *
     * @var \Slim\Helper\Set
     */
    public $block_helpers;

    /**
     * context
     *
     * @var SplStack
     */
    public $context;
    public $yield_blocks;

    /**
     * template_string
     *
     * @var string
     */
    protected $template_string;

    protected $extensions = array('.hbs', '.handlebars', '.mustache', '.html');
    protected $directories = array();

    public function __construct() {
        parent::__construct();

        $this->context = new SplStack();
        $this->yield_blocks = array();
        $this->helpers = new \Slim\Helper\Set($this->getHelpers());
        $this->block_helpers = new \Slim\Helper\Set($this->getBlockHelpers());
    }

    public function setTemplateString($string) {
        $this->template_string = $string;
    }

    public function setTemplatesDirectory($directory) {
        array_push($this->directories, $directory);
        return $this;
    }


    public function render($name, $data = array()) {
        $php = LightnCandy::compile($this->getTemplate($name), array(
            'flags' => LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_ERROR_LOG |
                LightnCandy::FLAG_INSTANCE |
                LightnCandy::FLAG_MUSTACHE |
                LightnCandy::FLAG_HANDLEBARS,
            'basedir' => $this->directories,
            'fileext' => $this->extensions,
            'helpers' => $this->helpers->all(),
            'hbhelpers' => $this->block_helpers->all()
        ));

        $renderer = LightnCandy::prepare($php);
        return $renderer(array_merge($data ?: array(), $this->all()), LCRun3::DEBUG_ERROR_LOG);
    }

    protected function getTemplate($name) {
        if (is_null($this->template_string)) {
            $this->setTemplateString( Module::template($name)->getCode() );
        }

        return $this->template_string;

        // foreach ($this->directories as $dir) {
        //     foreach ($this->extensions as $ext) {
        //         $path = $dir . DIRECTORY_SEPARATOR . ltrim($name . $ext, DIRECTORY_SEPARATOR);
        //         if (file_exists($path)) {
        //             return file_get_contents($path);
        //         }
        //     }
        // }
        // throw new NotFoundException("Template not found.");
    }

    protected function getHelpers() {
        $helpers = array(
            // core helpers
            'yield' => 'Hook\\View\\Helper::yieldContent',

            // string helpers
            'str_plural' => 'Hook\\View\\Helper::str_plural',
            'str_singular' => 'Hook\\View\\Helper::str_singular',
            'uppercase' => 'Hook\\View\\Helper::uppercase',
            'lowercase' => 'Hook\\View\\Helper::lowercase',
            'camel_case' => 'Hook\\View\\Helper::camel_case',
            'snake_case' => 'Hook\\View\\Helper::snake_case',

            // url helpers
            'public_url' => 'Hook\\View\\Helper::public_url',
            'link_to' => 'Hook\\View\\Helper::link_to',
            'stylesheet' => 'Hook\\View\\Helper::stylesheet',
            'javascript' => 'Hook\\View\\Helper::javascript',

            // form helpers
            'input' => 'Hook\\View\\Helper::input',
            'select' => 'Hook\\View\\Helper::select',

            // data helpers
            'count' => 'Hook\\View\\Helper::count',
            'config' => 'Hook\\View\\Helper::config',

            // miscelaneous
            'paginate' => 'Hook\\View\\Helper::paginate'
        );

        // $helper_files = glob(Router::config('templates.helpers_path') . '/*');
        // foreach($helper_files as $helper) {
        //     $helpers = array_merge($helpers, require($helper));
        // }

        return $helpers;
    }

    protected function getBlockHelpers() {
        return array(
            // core helpers
            'content_for' => 'Hook\\View\\BlockHelper::content_for',

            // url helpers
            'link_to' => 'Hook\\View\\BlockHelper::link_to',

            // form helpers
            'form' => 'Hook\\View\\BlockHelper::form',
            'form_for' => 'Hook\\View\\BlockHelper::form_for'
        );
    }

}

