<?php
namespace App\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmailService implements ConsumerInterface
{
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param AMQPMessage $msg
     * @return int|mixed
     */
    public function execute(AMQPMessage $msg) : int
    {
        $transport = $this->getTransport();
        $transport->send(unserialize($msg->body));
        $transport->stop();

        return ConsumerInterface::MSG_ACK;
    }

    /**
     * @return object|\Swift_Transport
     */
    protected function getTransport() : \Swift_Transport
    {
        /** @var \Swift_Transport $swiftTransport */
        $swiftTransport = $this->container->get('swiftmailer.mailer.default.transport.real');

        if (!$swiftTransport->isStarted()) {
            $swiftTransport->start();
        }

        return $swiftTransport;
    }
}