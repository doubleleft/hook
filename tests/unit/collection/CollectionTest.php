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
        echo "Let's create author!\n";
        $author = App::collection('authors')->create(array(
            'name' => "Endel " . uniqid()
        ));

        $book = App::collection('books')->create_new(array(
            'name' => "My book " . uniqid(),
            'author_id' => $author->_id
        ));
        echo "Let's save book!\n";
        $book->save();

        // var_dump(Book::with('author')->get()->toArray());

        // App::collection('testing')->each(function($row) {
        //     var_dump($row);
        // });

        // App::collection('testing')->each(function($row) {
        //     var_dump($row);
        // });

    }

}
