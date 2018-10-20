<?php

namespace App\Infrastructure\RabbitMQ;

use Doctrine\ORM\EntityManagerInterface;
use Kdyby\RabbitMq\IConsumer;
use PhpAmqpLib\Message\AMQPMessage;

abstract class BaseConsumer implements IConsumer
{

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function processMessage(AMQPMessage $message): int
    {
        $this->beforeProcess();

        $response = $this->process($message);

        $this->afterProcess();

        return $response;
    }

    protected function beforeProcess(): void
    {
        $connection = $this->entityManager->getConnection();

        if ($connection->ping() === false) {
            $connection->close();
            $connection->connect();
        }
    }

    protected abstract function process(AMQPMessage $message): int;

    protected function afterProcess(): void
    {
        $this->entityManager->clear();
    }
}