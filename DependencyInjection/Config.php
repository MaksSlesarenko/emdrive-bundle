<?php

namespace MaksSlesarenko\EmdriveBundle\DependencyInjection;

/**
 * Class Config
 * @package MaksSlesarenko\EmdriveBundle\DependencyInjection
 */
class Config
{
    public $serverName;

    public $logDir;

    public $pidDir;

    public $poolSize;

    public $cmdStart;

    public $cmdKill;

    public $tickInterval;

    public function __construct(array $config, $projectDir)
    {
        $this->serverName = $config['server_name'];
        $this->poolSize = $config['pool_size'];
        $this->cmdStart = $config['cmd_start'];
        $this->cmdKill = $config['cmd_kill'];
        $this->tickInterval = $config['tick_interval'];
        $this->logDir = $config['log_dir'];
        $this->pidDir = $config['pid_dir'];

        if ('/' !== $this->logDir[0]) {
            $this->logDir = $projectDir . '/' . $this->logDir;
        }

        if ('/' !== $this->pidDir[0]) {
            $this->pidDir = $projectDir . '/' . $this->pidDir;
        }
        $this->logDir = rtrim($this->logDir, '/') . '/';
        $this->pidDir = rtrim($this->pidDir, '/') . '/';
    }
}
