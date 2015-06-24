<?php

use Hook\View\View;
use Hook\Application\Config;

use Hook\Model\Module;

class ViewTest extends TestCase
{

    public function testSimple()
    {
        $string = Module::template('Hello {{ name }}.')->compile(array('name' => "Endel"));
        $this->assertTrue($string == "Hello Endel.");
    }

    public function testHelpers()
    {
        Config::set('repository.url', 'https://github.com/doubleleft/hook');
        Config::set('repository.author', 'doubleleft');
        $string = Module::template("{{ config 'repository.url' }} by {{config 'repository.author'}}.")->compile();
        $this->assertTrue($string == "https://github.com/doubleleft/hook by doubleleft.");

        $string = Module::template("{{ public_url }}")->compile();
        $this->assertTrue($string == "http://localhost/");
    }

}

