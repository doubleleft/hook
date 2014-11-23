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

    public function testMigrateRelationship()
    {
        Cache::flush();

        // books / authors / contacts
        Schema\Builder::migrate(App::collection('contacts')->getModel(), array(
            'relationships' => array('belongs_to' => 'author')
        ));
        Schema\Builder::migrate(App::collection('authors')->getModel(), array(
            'relationships' => array('has_many' => array('contacts'))
        ));

        $author = App::collection('authors')->create(array('name' => "Rasmus Lerdorf"));
        $this->assertTrue(get_class($author->contacts()) == "Illuminate\\Database\\Eloquent\\Relations\\HasMany");
    }

    public function testMigrateFields()
    {
        Cache::flush();

        $attributes = array(
            array(
                'name' => "default_is_string"
            ),
            array(
                'name' => "string",
                'type' => "string"
            ),
            array(
                'name' => "int",
                'type' => "integer"
            ),
            array(
                'name' => "float",
                'type' => "float"
            ),
            array(
                'name' => "boolean",
                'type' => "boolean"
            ),
        );

        // books / authors / contacts
        Schema\Builder::migrate(App::collection('schema')->getModel(), array(
            'attributes' => $attributes
        ));

        $dump = Schema\Builder::dump();
        $this->assertTrue(count($dump['schemas']['attributes']) == 5);
        $this->assertTrue($dump['schemas']['attributes'] == $attributes);

    }

}


