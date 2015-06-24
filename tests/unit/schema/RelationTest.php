<?php

use Hook\Database\Schema\Relation as Relation;

class RelationTest extends TestCase
{

    public function testSingle()
    {
        $config_0 = $this->parse("belongs_to: author");
        $fields_0 = Relation::sanitize('dummy_collection', key($config_0), current($config_0));
        $this->assertTrue($fields_0 == array(
            'author' => array(
                'collection' => 'authors',
                'foreign_key' => 'author_id',
                'primary_key' => '_id',
                'required' => false,
                'on_delete' => 'none',
                'on_update' => 'none',
            )
        ));

        $config_1 = $this->parse(<<<EOF
belongs_to:
  - author
EOF
);
        $fields_1 = Relation::sanitize('dummy_collection', key($config_1), current($config_1));
        $this->assertTrue($fields_1 == array(
            'author' => array(
                'collection' => 'authors',
                'foreign_key' => 'author_id',
                'primary_key' => '_id',
                'required' => false,
                'on_update' => 'none',
                'on_delete' => 'none',
            )
        ));

        $fields_2 = Relation::sanitize('dummy_collection', 'belongs_to', array('authors'));
        $this->assertTrue($fields_2 == array(
            'author' => array(
                'collection' => 'authors',
                'foreign_key' => 'author_id',
                'primary_key' => '_id',
                'required' => false,
                'on_update' => 'none',
                'on_delete' => 'none',
            )
        ));

        $fields_3 = Relation::sanitize('dummy_collection', 'belongs_to', array('authors', 'publishers'));
        $this->assertTrue($fields_3 == array(
            'author' => array(
                'collection' => 'authors',
                'foreign_key' => 'author_id',
                'primary_key' => '_id',
                'required' => false,
                'on_update' => 'none',
                'on_delete' => 'none',
            ),
            'publisher' => array(
                'collection' => 'publishers',
                'foreign_key' => 'publisher_id',
                'primary_key' => '_id',
                'required' => false,
                'on_update' => 'none',
                'on_delete' => 'none',
            )
        ));

        $fields_5 = Relation::sanitize('dummy_collection', 'has_many', array('author'));
        $this->assertTrue($fields_5 == array(
            'authors' => array(
                'collection' => 'authors',
                'foreign_key' => 'dummy_collection_id',
                'primary_key' => '_id'
            )
        ));
    }

    public function testBelongsToConfig()
    {
        $belongs_to1 = $this->parse(<<<EOF
belongs_to:
  - author:
      collection: auth
      required: true
      on_delete: restrict
      on_update: cascade
EOF
);

        $fields_1 = Relation::sanitize('dummy_collection', key($belongs_to1), current($belongs_to1));
        $this->assertTrue($fields_1 == array(
            'author' => array(
                'collection'=> 'auths',
                'foreign_key' => 'author_id',
                'primary_key' => '_id',
                'required' => true,
                'on_delete' => 'restrict',
                'on_update' => 'cascade'
            )
        ));

        $belongs_to2 = $this->parse(<<<EOF
belongs_to:
  - creator:
      collection: auth
  - author:
      collection: auth
EOF
);

        $fields_2 = Relation::sanitize('dummy_collection', key($belongs_to2), current($belongs_to2));
        $this->assertTrue($fields_2 == array(
            'creator' => array(
                'collection'=> 'auths',
                'foreign_key' => 'creator_id',
                'primary_key' => '_id',
                'required' => false,
                'on_delete' => 'none',
                'on_update' => 'none'
            ),
            'author' => array(
                'collection'=> 'auths',
                'foreign_key' => 'author_id',
                'primary_key' => '_id',
                'required' => false,
                'on_delete' => 'none',
                'on_update' => 'none'
            )
        ));

    }

    public function testHasManyConfig()
    {
        $belongs_to1 = $this->parse(<<<EOF
has_many:
  - authors:
      collection: auth
EOF
);

        $fields_1 = Relation::sanitize('dummy_collection', key($belongs_to1), current($belongs_to1));
        $this->assertTrue($fields_1 == array(
            'authors' => array(
                'collection'=> 'auths',
                'foreign_key' => 'dummy_collection_id',
                'primary_key' => '_id',
            )
        ));

        $belongs_to2 = $this->parse(<<<EOF
has_many:
  - creator:
      collection: auth
  - authors:
      collection: auth
EOF
);

        $fields_2 = Relation::sanitize('dummy_collection', key($belongs_to2), current($belongs_to2));
        $this->assertTrue($fields_2 == array(
            'creators' => array(
                'collection'=> 'auths',
                'foreign_key' => 'dummy_collection_id',
                'primary_key' => '_id'
            ),
            'authors' => array(
                'collection'=> 'auths',
                'foreign_key' => 'dummy_collection_id',
                'primary_key' => '_id'
            )
        ));

    }

    protected function parse($str) {
        $parser = new Symfony\Component\Yaml\Parser();
        return $parser->parse($str);
    }

}

