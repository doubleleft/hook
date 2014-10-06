<?php

use Hook\Model\App as App;
use Hook\Database\Schema as Schema;
use Hook\Cache\Cache as Cache;
use Hook\Database\Schema\Cache as SchemaCache;

class SchemaBuilderTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    public function testMigrate()
    {
        Cache::flush();

        // books / authors / contacts
        Schema\Builder::migrate(App::collection('contacts')->getModel(), array(
            'relationships' => array('belongs_to' => 'author')
        ));
        Schema\Builder::migrate(App::collection('authors')->getModel(), array(
            'relationships' => array('has_many' => array('contacts'))
        ));

        SchemaCache::flush();

        $author = App::collection('authors')->create(array('name' => "Rasmus Lerdorf"));
        $this->assertTrue(get_class($author->contacts()) == "Illuminate\\Database\\Eloquent\\Relations\\HasMany");
    }

}


