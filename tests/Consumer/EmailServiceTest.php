<?php

namespace App\Tests\Consumer;

use App\Entity\Email;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmailServiceTest extends WebTestCase
{
    /**
     * Testing EmailService Consumer
     */
    public function testService()
    {
        self::bootKernel();

        $title = 'test title '.rand(0 , 999999999);

        $message = (new \Swift_Message($title))
            ->setFrom('send@example.com')
            ->setTo('william_wilson@gmail.com')
            ->setBody(
                'test content'
            )
        ;

        $AMQPMessage = new AMQPMessage();
        $AMQPMessage->setBody(serialize($message));

        $consumer = self::$container->get('email_service');
        $consumer->execute($AMQPMessage);

        $emailLog = self::$container->get('doctrine')->getRepository(Email::class)->findOneByTitle($title);

        $this->assertInstanceOf(Email::class, $emailLog);
        $this->assertSame('william_wilson@gmail.com', $emailLog->getEmail());
        $this->assertSame('test content', $emailLog->getContent());
    }
}