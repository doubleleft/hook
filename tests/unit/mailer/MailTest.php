<?php use Hook\Mailer\Mail;

class MailTest extends TestCase
{

    public function testMessage()
    {
        $message = Mail::message('{{ text }}, using Module::template.', array('text' => "Body text"));
        $this->assertTrue($message->getBody() == 'Body text, using Module::template.');
    }

    public function testMessageAttachments()
    {
        $message = Mail::message('Body text');
        $message->attach('../logo.png');

        $attached_children = $message->getChildren();
        $this->assertTrue(count($attached_children) == 1);
        $this->assertTrue($attached_children[0] instanceof Swift_Attachment);

        $attachment = Mail::attachment('../body.png');
        $this->assertTrue($attachment instanceof Swift_Attachment);
        $message->attach($attachment);

        $attached_children = $message->getChildren();
        $this->assertTrue(count($attached_children) == 2);
    }

    public function testSendArray() {
        $sent = Mail::send(array(
            'to' => 'edreyer@doubleleft.com',
            'body' => "Testing",
            'from' => 'edreyer@doubleleft.com'
        ));
    }

}

