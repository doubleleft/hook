<?php

use Hook\View\Template;

class TemplateTest extends TestCase
{

    public function testTemplate()
    {
        $now = time() - 60 * 8;

        $template = new Template();

        $template->compile('Hello {{ name }}.');
        $this->assertTrue($template->render(array('name' => "Endel")) == "Hello Endel.");

        $template->compile("Changed code here.");
        $this->assertFalse($template->render(array('name' => "Endel")) == "Hello Endel.");
    }

}

