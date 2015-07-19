<?php

use Hook\Model\App as App;
use Hook\Database\Schema as Schema;
use Hook\Database\Relationship as Relationship;
use Hook\Database\CollectionDelegator as CollectionDelegator;
use Hook\Cache\Cache as Cache;

class CollectionRelationshipTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        Cache::flush();

        // books / authors / contacts
        Schema\Builder::getInstance()->migrate(App::collection('contacts')->getModel(), array(
            'relationships' => array('belongs_to' => 'author')
        ));
        Schema\Builder::getInstance()->migrate(App::collection('authors')->getModel(), array(
            'relationships' => array('has_many' => array('books', 'contacts'))
        ));
        Schema\Builder::getInstance()->migrate(App::collection('books')->getModel(), array(
            'relationships' => array('belongs_to' => array('author'))
        ));

        // clear tables before running tests
        App::collection('authors')->truncate();
        App::collection('books')->truncate();
        App::collection('contacts')->truncate();

        // populate simple data
        $author = App::collection('authors')->create(array('name' => "Rasmus Lerdorf"));
        $author->contacts()->create(array('name' => "Kevin Tatroe"));
        $author->contacts()->create(array('name' => "Peter MacIntyre"));

        // default create
        $book = App::collection('books')->create(array(
            'name' => "Programming PHP",
            'author_id' => $author->_id
        ));
        $this->assertFalse(isset($book['author']), "shouldn't eager load related data by default");

        $eager_loaded_book = CollectionDelegator::queryEagerLoadRelations($book, array('author'));
        $this->assertTrue($eager_loaded_book['author']['name'] == "Rasmus Lerdorf", "should eager load related data");

        $book_with_author = App::collection('books')->join("author")->create(array(
            'name' => "Programming PHP",
            'author_id' => $author->_id
        ));
        $this->assertTrue($book_with_author['author']['name'] == "Rasmus Lerdorf", "should eager load related data");

        // teams / matches
        Schema\Builder::getInstance()->migrate(App::collection('matches')->getModel(), array(
            'relationships' => array('belongs_to' => array(
                array(
                    'house' => array(
                        'collection' => 'teams'
                    ),
                ),
                array(
                    'guest' => array(
                        'collection' => 'teams'
                    )
                )
            ))
        ));
        Schema\Builder::getInstance()->migrate(App::collection('teams')->getModel(), array(
            'relationships' => array('has_many' => 'matches')
        ));

        App::collection('teams')->truncate();
        App::collection('matches')->truncate();

        $brazil = App::collection('teams')->create(array('name' => "Brazil"));
        $germany = App::collection('teams')->create(array('name' => "Germany"));
        $argentina = App::collection('teams')->create(array('name' => "Argentina"));
        $netherlands = App::collection('teams')->create(array('name' => "Netherlands"));

        App::collection('matches')->create(array(
            'name' => "Brazil vs Germany",
            'house_id' => $brazil->_id,
            'guest_id' => $germany->_id,
        ));

        App::collection('matches')->create(array(
            'name' => "Argentina vs Netherlands",
            'house_id' => $argentina->_id,
            'guest_id' => $netherlands->_id,
        ));

    }

    public function testBooksAndAuthors()
    {
        $books = App::collection('books')->join('author')->toArray();
        $this->assertTrue(count($books) == 2);
        $this->assertTrue($books[0]['name'] == "Programming PHP");
        $this->assertTrue($books[0]['author']['name'] == "Rasmus Lerdorf");

        $books_author_contacts = App::collection('books')->join('author.contacts')->toArray();
        $this->assertTrue(count($books_author_contacts) == 2);
        $this->assertTrue($books_author_contacts[0]['name'] == "Programming PHP");
        $this->assertTrue($books_author_contacts[0]['author']['name'] == "Rasmus Lerdorf");
        $this->assertTrue(count($books_author_contacts[0]['author']['contacts']) == 2);
        $this->assertTrue(gettype($books_author_contacts[0]['author']['contacts'][0]['name']) == 'string');
        $this->assertTrue(gettype($books_author_contacts[0]['author']['contacts'][1]['name']) == 'string');

        $authors_books_contacts = App::collection('author')->join('books', 'contacts')->toArray();
        $this->assertTrue(count($authors_books_contacts) == 1);
        $this->assertTrue($authors_books_contacts[0]['name'] == "Rasmus Lerdorf");
        $this->assertTrue(count($authors_books_contacts[0]['books']) == 2);
        $this->assertTrue($authors_books_contacts[0]['books'][0]['name'] == "Programming PHP");
        $this->assertTrue(count($authors_books_contacts[0]['contacts']) == 2);
        $this->assertTrue(gettype($authors_books_contacts[0]['contacts'][0]['name']) == 'string');
        $this->assertTrue(gettype($authors_books_contacts[0]['contacts'][1]['name']) == 'string');
    }

    public function testTeamAndMatches()
    {
        $matches = App::collection('matches')->toArray();
        $this->assertTrue(count($matches) == 2);
        $this->assertTrue(isset($matches[0]['house']) == false);
        $this->assertTrue(isset($matches[0]['guest']) == false);

        $matches = App::collection('matches')->join('house', 'guest')->toArray();
        $this->assertTrue(count($matches) == 2);
        $this->assertTrue($matches[0]['house']['name'] == "Brazil");
        $this->assertTrue($matches[0]['guest']['name'] == "Germany");
    }

    public function testDirectAssociation()
    {
        $match_1 = App::collection('matches')->create(array(
            'name' => "Team one VS Team two",
            'house' => array('name' => "One"),
            'guest' => array('name' => "Two")
        ));

        $three = App::collection('teams')->create(array('name' => "Three"));
        $four = App::collection('teams')->create(array('name' => "Four"));
        $match_2 = App::collection('matches')->create(array(
            'name' => "Team three VS Team four",
            'house' => $three,
            'guest' => $four
        ));

        // retrieve recent-created matches with relationships
        $matches = App::collection('matches')
            ->where('_id', '>=', $match_1->_id)
            ->join('house', 'guest')
            ->toArray();
        $this->assertTrue($matches[0]['house']['name'] == "One");
        $this->assertTrue($matches[0]['guest']['name'] == "Two");
        $this->assertTrue($matches[1]['house']['name'] == "Three");
        $this->assertTrue($matches[1]['guest']['name'] == "Four");

        // from related direction
        App::collection('authors')->create(array(
            'name' => "Somebody",
            'contacts' => array(
                array('name' => "Contact 1"),
                array('name' => "Contact 2"),
                array('name' => "Contact 3"),
                array('name' => "Contact 4"),
                array('name' => "Contact 5")
            )
        ));

        $authors_and_contacts = App::collection('authors')->join('contacts')->toArray();
        $this->assertTrue(count($authors_and_contacts) == 2);
        $this->assertTrue(count($authors_and_contacts[0]['contacts']) == 2);

        $this->markTestIncomplete("This feature doesn't work yet.");
        $this->assertTrue(count($authors_and_contacts[1]['contacts']) == 5);
    }

}

