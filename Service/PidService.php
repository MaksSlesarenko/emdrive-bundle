<?php

namespace MaksSlesarenko\EmdriveBundle\Service;

use MaksSlesarenko\EmdriveBundle\Command\LockableCommandInterface;
use MaksSlesarenko\EmdriveBundle\DependencyInjection\Config;

class PidService
{
    /**
     * @var LockService
     */
    private $lockService;

    /**
     * @var string
     */
    private $dir;

    public function __construct(LockService $lockService, Config $config)
    {
        $this->lockService = $lockService;

        $this->dir = rtrim($config->pidDir, '/');
    }

    public function getPid(LockableCommandInterface $command)
    {
        $filename = $this->getFilename($command);
        if (file_exists($filename)) {
            return file_get_contents($filename);
        }
        return '';
    }

    public function save(LockableCommandInterface $command)
    {
        $filename = $this->getFilename($command);
        touch($filename);
        $fp = fopen($filename, 'r+');
        fputs($fp, \getmypid());
    }

    public function remove(LockableCommandInterface $command)
    {
        $filename = $this->getFilename($command);
        if (file_exists($filename)) {
            file_put_contents($filename, '');
        }
    }

    private function getFilename(LockableCommandInterface $command)
    {
        return sprintf('%s/%s.pid', $this->dir, preg_replace('/[^a-z0-9\._-]+/i', '-', $command->getLockName()));
    }
}
