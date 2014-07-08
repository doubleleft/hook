<?php

use Hook\Database\Schema\Relation as Relation;

class RelationTest extends TestCase
{

    public function testSingle()
    {
        $config_0 = $this->parse("belongs_to: author");
        $fields_0 = Relation::sanitize(key($config_0), current($config_0));
        $this->assertTrue($fields_0 == array('author' => 'authors'));

        $config_1 = $this->parse(<<<EOF
belongs_to:
  - author
EOF
);
        $fields_1 = Relation::sanitize(key($config_1), current($config_1));
        $this->assertTrue($fields_1 == array('author' => 'authors'));

        $fields_2 = Relation::sanitize('belongs_to', array('authors'));
        $this->assertTrue($fields_2 == array('author' => 'authors'));

        $fields_3 = Relation::sanitize('belongs_to', array('authors', 'publishers'));
        $this->assertTrue($fields_3 == array('author' => 'authors', 'publisher' => 'publishers'));

        $fields_4 = Relation::sanitize('belongs_to', array('author1' => 'author', 'author2' => 'author'));
        $this->assertTrue($fields_4 == array('author1' => 'authors', 'author2' => 'authors'));

        $fields_5 = Relation::sanitize('has_many', array('author'));
        $this->assertTrue($fields_5 == array('authors' => 'authors'));
    }

    public function testMultiple()
    {
        $belongs_to1 = $this->parse(<<<EOF
belongs_to:
  - team_1: teams
  - team_2: team
  - team
EOF
);
        $fields_1 = Relation::sanitize(key($belongs_to1), current($belongs_to1));
        $this->assertTrue($fields_1 == array(
            'team_1' => 'teams',
            'team_2' => 'teams',
            'team' => 'teams',
        ));

        $belongs_to2 = $this->parse(<<<EOF
belongs_to:
  team_1: teams
  team_2: team
EOF
);
        $fields_2 = Relation::sanitize(key($belongs_to2), current($belongs_to2));
        $this->assertTrue($fields_2 == array('team_1' => 'teams', 'team_2' => 'teams'));

    }

    protected function parse($str) {
        $parser = new Symfony\Component\Yaml\Parser();
        return $parser->parse($str);
    }

}

