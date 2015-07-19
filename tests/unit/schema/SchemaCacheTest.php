<?php

use Hook\Model\App as App;
use Hook\Database\Schema as Schema;
use Hook\Cache\Cache as Cache;
use Hook\Database\Schema\Cache as SchemaCache;

class SchemaCacheTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        Cache::flush();
    }

    public function testMigrateDynamic()
    {
        Schema\Builder::getInstance()->migrate(App::collection('schema_cache')->getModel(), array(
            'attributes' => array(
                array('name' => "string_field")
            ),
            'relationships' => array('has_many' => array('contacts'))
        ));
        App::collection('schema_cache')->create(array('new_field' => 10));

        $this->assertTrue( SchemaCache::get('schema_caches') == array(
            'attributes' => array(
                array("name" => "string_field"),
                array("name" => "new_field", "type" => "integer")
            ),
            'relationships' => array(
                'has_many' => array(
                    'contacts' => array(
                        'collection' => 'contacts',
                        'foreign_key' => 'schema_cach_id',
                        'primary_key' => '_id'
                    )
                )
            ),
            'lock_attributes' => false
        ));
    }

    public function testMigrateAddFields() {
        Schema\Builder::getInstance()->migrate(App::collection('schema_cache')->getModel(), array(
            'attributes' => array(
                array('name' => "first")
            ),
        ));
        Schema\Builder::getInstance()->migrate(App::collection('schema_cache')->getModel(), array(
            'attributes' => array(
                array('name' => "first"),
                array('name' => "second"),
            ),
        ));

        $this->assertTrue( SchemaCache::get('schema_caches') == array(
            'attributes' => array(
                array("name" => "first"),
                array("name" => "second")
            ),
            'relationships' => array(),
            'lock_attributes' => false
        ));
    }

    public function testMigrateRemoveFields() {
        Schema\Builder::getInstance()->migrate(App::collection('schema_cache')->getModel(), array(
            'attributes' => array(
                array('name' => "first"),
                array('name' => "second")
            ),
        ));
        Schema\Builder::getInstance()->migrate(App::collection('schema_cache')->getModel(), array(
            'attributes' => array(
                array('name' => "second"),
            ),
        ));
        $this->assertTrue( SchemaCache::get('schema_caches') == array(
            'attributes' => array(
                array("name" => "second")
            ),
            'relationships' => array(),
            'lock_attributes' => false
        ));

        Schema\Builder::getInstance()->migrate(App::collection('schema_cache')->getModel(), array(
            'attributes' => array(
                array('name' => "first"),
                array('name' => "second"),
                array('name' => "third"),
                array('name' => "four"),
            ),
        ));
        $this->assertTrue( SchemaCache::get('schema_caches') == array(
            'attributes' => array(
                array("name" => "second"),
                array("name" => "first"),
                array("name" => "third"),
                array("name" => "four"),
            ),
            'relationships' => array(),
            'lock_attributes' => false
        ));

        Schema\Builder::getInstance()->migrate(App::collection('schema_cache')->getModel(), array(
            'attributes' => array(
                array('name' => "four"),
            ),
        ));
        $this->assertTrue( SchemaCache::get('schema_caches') == array(
            'attributes' => array(
                array("name" => "four"),
            ),
            'relationships' => array(),
            'lock_attributes' => false
        ));
    }

    public function testMigrateAddRelationship() {
        Schema\Builder::getInstance()->migrate(App::collection('schema_cache')->getModel(), array(
            'relationships' => array('has_many' => array('contacts'))
        ));
        Schema\Builder::getInstance()->migrate(App::collection('schema_cache')->getModel(), array(
            'relationships' => array('has_many' => array('contacts'))
        ));

        $this->assertTrue( SchemaCache::get('schema_caches') == array(
            'attributes' => array(),
            'relationships' => array(
                'has_many' => array(
                    'contacts' => array(
                        'collection' => 'contacts',
                        'foreign_key' => 'schema_cach_id',
                        'primary_key' => '_id'
                    )
                )
            ),
            'lock_attributes' => false
        ));

    }

}
