<?php

use Hook\View\Template;

class TemplateTest extends TestCase
{

    public function testTemplate()
    {
        $now = time() - 60 * 8;
        $template_data = array(
            'filename' => 'my_template',
            'code' => 'Hello {{ name }}.',
            'updated_at' => $now
        );

        $template1 = new Template($template_data);
        $this->assertTrue($template1->render(array('name' => "Endel")) == "Hello Endel.");

        $template_data2 = array_merge(array(), $template_data); // clone
        $template_data2['code'] = "Changed code here.";
        $template2 = new Template($template_data2);
        $this->assertFalse($template2->render(array('name' => "Endel")) == "Hello Endel.");

        $template_data3 = array_merge(array(), $template_data); // clone
        $template_data3['code'] = "Changed code here.";
        $template3 = new Template($template_data3);
        $this->assertTrue($template3->render(array('name' => "Endel")) == "Changed code here.");
    }

}

