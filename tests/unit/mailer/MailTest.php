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

    public function testMethods()
    {
        $message = Mail::message();

        $message->body('body');
        $this->assertTrue($message->getBody() == 'body');

        $message->contentType('text/html');
        $this->assertTrue($message->getContentType() == 'text/html');

        $message->subject('subject');
        $this->assertTrue($message->getSubject() == 'subject');

        $date = time();
        $message->date($date);
        $this->assertTrue($message->getDate() == $date);

        $message->returnPath('return@doubleleft.com');
        $this->assertTrue($message->getReturnPath() == 'return@doubleleft.com');

        $message->from('from@email.com');
        $this->assertTrue($message->getFrom() == array("from@email.com" => NULL));

        $message->from('from@email.com', "From");
        $this->assertTrue($message->getFrom() == array("from@email.com" => "From"));

        $message->replyTo('reply@doubleleft.com');
        $this->assertTrue($message->getReplyTo() == array('reply@doubleleft.com' => NULL));

        $message->to('edreyer@doubleleft.com');
        $this->assertTrue($message->getTo() == array('edreyer@doubleleft.com' => NULL));

        $message->cc('cc@doubleleft.com');
        $this->assertTrue($message->getCc() == array('cc@doubleleft.com' => NULL));

        $message->bcc('bcc@doubleleft.com');
        $this->assertTrue($message->getBcc() == array('bcc@doubleleft.com' => NULL));
    }

    public function testSendArray() {
        // Mail::send(array(
        //     'to' => 'edreyer@doubleleft.com',
        //     'subject' => "hook mail",
        //     'body' => "Testing",
        //     'from' => 'edreyer@doubleleft.com'
        // ));
    }

}

