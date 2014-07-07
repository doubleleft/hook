<?php

use API\Model\App as App;
use API\Database\Schema as Schema;
use API\Cache\Cache as Cache;

class CollectionRelationshipTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        Cache::flush();

        // setup schema
        Schema\Builder::migrate(App::collection('authors')->getModel(), array(
            'relationships' => array('has_many' => 'books')
        ));

        Schema\Builder::migrate(App::collection('books')->getModel(), array(
            'relationships' => array('belongs_to' => 'author')
        ));

        // clear tables before running tests
        // App::collection('authors')->truncate();
        // App::collection('books')->truncate();

        // populate simple data
        $author = App::collection('authors')->create(array('name' => "Rasmus Lerdorf"));
        App::collection('books')->create(array(
            'name' => "Programming PHP",
            'author_id' => $author->_id
        ));
    }

    public function testRelationships()
    {
        var_dump(App::collection('books')->with('author')->toArray());
        var_dump(App::collection('author')->with('books')->get()->toArray());
    }

}

