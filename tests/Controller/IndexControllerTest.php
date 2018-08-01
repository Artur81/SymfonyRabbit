<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
    /**
     * Presentation of the form
     */
    public function testShowForm()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Sending the correct form
     */
    public function testPostCorrectForm()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $form = $crawler->selectButton('Submit')->form();

        $form['contact[email]'] = 'william_wilson@gmail.com';
        $form['contact[title]'] = 'test';
        $form['contact[content]'] = 'test';

        $client->enableProfiler();
        $client->submit($form);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // checks that an email was sent (added to the RabbitMq)
        $this->assertSame(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Asserting email data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertSame('test', $message->getSubject());
        $this->assertSame('william_wilson@gmail.com', key($message->getTo()));
        $this->assertSame('test', $message->getBody());
    }

    /**
     * Testing Validation
     */
    public function testFormValidation()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $form = $crawler->selectButton('Submit')->form();

        $form['contact[email]'] = 'william_wilson@hjgfcgfdesxdfwsza.pl';
        $form['contact[title]'] = 'test.kjhbyjtgfcrgdeszwrxhgjvbkjlbnkujgvchrtdxszdfrswzQAFdhcghjkb kjhlbjvbgtyrfhdxrw';
        $form['contact[content]'] = '';

        $client->enableProfiler();
        $client->submit($form);

        $validateCollector = $client->getProfile()->getCollector('validator');

        $this->assertSame(3, $validateCollector->getViolationsCount());
    }
}