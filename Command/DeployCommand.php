<?php

namespace EmdriveBundle\Command;

use EmdriveBundle\DependencyInjection\Config;
use EmdriveBundle\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Deploy Command
 */
class DeployCommand extends Command
{
    /**
     * @var Config
     */
    private $config;

    use LoggerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('emdrive:deploy')
            ->setDescription(
                'Deploy'
            )
         ;

        parent::configure();
    }

    /**
     * @required
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();
        if (!is_dir($this->config->logDir)) {
            $filesystem->mkdir($this->config->logDir);
            $filesystem->chmod($this->config->logDir, 0777);
        }

        if (!is_dir($this->config->pidDir)) {
            $filesystem->mkdir($this->config->pidDir);
            $filesystem->chmod($this->config->pidDir, 0777);
        }

        $commands = [];
        foreach ($this->getApplication()->all() as $command) {
            if ($command instanceof DeploymentCommandIterface) {
                $commands[$command->getName()] = $command->getDeployPriority();
            }
        }

        asort($commands);

        /** @var $command DeploymentCommandIterface */
        foreach (array_keys($commands) as $commandName) {
            $command = $this->getApplication()->get($commandName);

            $input = new ArrayInput([]);
            $command->run($input, $output);
        }
    }
}
