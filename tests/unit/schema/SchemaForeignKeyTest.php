<?php

use Hook\Model\App as App;
use Hook\Database\Schema as Schema;
use Hook\Cache\Cache as Cache;
use Hook\Database\Schema\Cache as SchemaCache;

class SchemaForeignKeyTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    public function testBelongsToIndex()
    {
        Cache::flush();

        // Schema\Builder::getInstance()->migrate(App::collection('auths')->getModel(), array(
        //     'attributes' => array(
        //         array(
        //             'name' => 'lucky_number_id',
        //             'type' => 'integer',
        //             'unique' => true
        //         )
        //     ),
        //     'relationships' => array(
        //         'belongs_to' => array(
        //             'lucky_numbers',
        //             'authors' => array(
        //
        //             )
        //         )
        //     )
        // ));
        //
        // $auths_cache = SchemaCache::get('auths');
        // $this->assertTrue($auths_cache['attributes'][0]['name'] == 'lucky_number_id');
    }

}
