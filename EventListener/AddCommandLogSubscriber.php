<?php

namespace MaksSlesarenko\EmdriveBundle\EventListener;

use MaksSlesarenko\EmdriveBundle\DependencyInjection\Config;
use MaksSlesarenko\EmdriveBundle\LoggerAwareTrait;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

class AddCommandLogSubscriber implements EventSubscriberInterface
{
    use LoggerAwareTrait;

    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND      => ['onStart', 30],
        ];
    }

    public function onStart(ConsoleCommandEvent $event)
    {
        $logDir = $this->config->logDir . date('Y_m_d/');

        $filesystem = new Filesystem();
        $filesystem->mkdir($logDir);

        $errorLogHandler = new \Monolog\Handler\StreamHandler($logDir . 'error.log', \Monolog\Logger::ERROR);
        $errorLogHandler->setFormatter(
            new \Monolog\Formatter\LineFormatter(null, 'H:i:s', false, true)
        );

        $this->logger->pushHandler($errorLogHandler);


        $commandName = preg_replace('/\W+/', '_', $event->getCommand()->getName());

        $errorLogHandler = new \Monolog\Handler\StreamHandler($logDir . $commandName . '.log', \Monolog\Logger::INFO);
        $errorLogHandler->setFormatter(
            new \Monolog\Formatter\LineFormatter(null, 'H:i:s', false, true)
        );

        $this->logger->pushHandler($errorLogHandler);
    }
}
