<?php

use API\Model\App as App;

class Book extends API\Model\Collection {
    protected $table = 'books';

    function author() {
        return $this->belongsTo('Author');
    }
};

class Author extends API\Model\Collection {
    protected $table = 'authors';
};

class CollectionTest extends TestCase
{

    public function testCreate()
    {
        // $author = App::collection('authors')->create(array(
        //     'name' => "Endel " . uniqid()
        // ));
        //
        // $book = App::collection('books')->create(array(
        //     'name' => "My book " . uniqid(),
        //     'author_id' => $author->_id
        // ));

        App::collection('books')->with('author')->each(function($row) {
            var_dump($row->toArray());
        });

        // App::collection('testing')->each(function($row) {
        //     var_dump($row);
        // });

        // App::collection('testing')->each(function($row) {
        //     var_dump($row);
        // });

    }

}
