<?php
namespace App\Consumer;

use App\Entity\Email;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmailService implements ConsumerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * EmailService constructor.
     * @param ContainerInterface $container
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ContainerInterface $container, EntityManagerInterface $entityManager)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    /**
     * @param AMQPMessage $msg
     * @return int|mixed
     */
    public function execute(AMQPMessage $msg) : int
    {
        $message = unserialize($msg->body);

        $this->logEmail($message);

        $transport = $this->getTransport();
        $transport->send($message);
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

    /**
     * @param \Swift_Mime_SimpleMessage $message
     */
    protected function logEmail(\Swift_Mime_SimpleMessage $message) : void
    {
        $emailLog = new Email();
        $emailLog->setEmail(implode(',',array_keys($message->getTo())));
        $emailLog->setTitle($message->getSubject());
        $emailLog->setContent($message->getBody());
        $emailLog->setCreatedAt(new \DateTime());

        $this->entityManager->persist($emailLog);
        $this->entityManager->flush();
    }
}