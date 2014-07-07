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

        Schema\Builder::migrate(App::collection('contacts')->getModel(), array(
            'relationships' => array('belongs_to' => 'author')
        ));

        Schema\Builder::migrate(App::collection('authors')->getModel(), array(
            'relationships' => array('has_many' => array('books', 'contacts'))
        ));

        Schema\Builder::migrate(App::collection('books')->getModel(), array(
            'relationships' => array('belongs_to' => 'author')
        ));

        // clear tables before running tests
        App::collection('authors')->truncate();
        App::collection('books')->truncate();
        App::collection('contacts')->truncate();

        // populate simple data
        $author = App::collection('authors')->create(array('name' => "Rasmus Lerdorf"));
        $author->contacts()->create(array('name' => "Kevin Tatroe"));
        $author->contacts()->create(array('name' => "Peter MacIntyre"));

        App::collection('books')->create(array(
            'name' => "Programming PHP",
            'author_id' => $author->_id
        ));
    }

    public function testRelationships()
    {
        $books = App::collection('books')->with('author')->toArray();
        $this->assertTrue(count($books) == 1);
        $this->assertTrue($books[0]['name'] == "Programming PHP");
        $this->assertTrue($books[0]['author']['name'] == "Rasmus Lerdorf");

        $books_author_contacts = App::collection('books')->with('author.contacts')->toArray();
        $this->assertTrue(count($books_author_contacts) == 1);
        $this->assertTrue($books_author_contacts[0]['name'] == "Programming PHP");
        $this->assertTrue($books_author_contacts[0]['author']['name'] == "Rasmus Lerdorf");
        $this->assertTrue(count($books_author_contacts[0]['author']['contacts']) == 2);
        $this->assertTrue($books_author_contacts[0]['author']['contacts'][0]['name'] == "Kevin Tatroe");
        $this->assertTrue($books_author_contacts[0]['author']['contacts'][1]['name'] == "Peter MacIntyre");

        $authors_books_contacts = App::collection('author')->with('books', 'contacts')->toArray();
        $this->assertTrue(count($authors_books_contacts) == 1);
        $this->assertTrue($authors_books_contacts[0]['name'] == "Rasmus Lerdorf");
        $this->assertTrue(count($authors_books_contacts[0]['books']) == 1);
        $this->assertTrue($authors_books_contacts[0]['books'][0]['name'] == "Programming PHP");
        $this->assertTrue(count($authors_books_contacts[0]['contacts']) == 2);
        $this->assertTrue($authors_books_contacts[0]['contacts'][0]['name'] == "Kevin Tatroe");
        $this->assertTrue($authors_books_contacts[0]['contacts'][1]['name'] == "Peter MacIntyre");
    }

}

