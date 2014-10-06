<?php

class StrTest extends TestCase
{

    public function testStrings()
    {
        $this->assertTrue( str_singular('match') == 'match' );
        $this->assertTrue( str_singular('matches') == 'match' );
        $this->assertTrue( str_plural('matches') == 'matches' );
        $this->assertTrue( str_plural('match') == 'matches' );
    }

}


