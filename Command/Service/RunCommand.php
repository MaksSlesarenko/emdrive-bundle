<?php

namespace MaksSlesarenko\EmdriveBundle\Command\Service;

use MaksSlesarenko\EmdriveBundle\Command\LockableCommandInterface;
use MaksSlesarenko\EmdriveBundle\Command\ScheduledCommandInterface;
use MaksSlesarenko\EmdriveBundle\DependencyInjection\Config;
use MaksSlesarenko\EmdriveBundle\InterruptableExecutionTrait;
use MaksSlesarenko\EmdriveBundle\LockableExecutionTrait;
use MaksSlesarenko\EmdriveBundle\LoggerAwareTrait;
use MaksSlesarenko\EmdriveBundle\Service\PidService;
use MaksSlesarenko\EmdriveBundle\Service\ScheduleService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunCommand
 *
 * @package MaksSlesarenko\EmdriveBundle\Command\Service
 */
class RunCommand extends Command implements LockableCommandInterface
{
    use LockableExecutionTrait;
    use InterruptableExecutionTrait;
    use LoggerAwareTrait;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScheduleService
     */
    private $schedule;

    /**
     * @required
     * @param Config $schedule
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @required
     * @param ScheduleService $schedule
     */
    public function setSchedule(ScheduleService $schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * @var PidService
     */
    private $pidService;

    /**
     * @required
     * @param PidService $pidService
     */
    public function setPidService(PidService $pidService)
    {
        $this->pidService = $pidService;
    }

    public function getLockName()
    {
        return $this->getName() . $this->config->serverName;
    }

    protected function configure()
    {
        $this
            ->setName('emdrive:service:run')
            ->setDescription(
                'Start service'
            )
            ->addOption('poolSize', null, InputOption::VALUE_REQUIRED, 'Pool size')
        ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $availableCommands = [];
        foreach ($this->getApplication()->all() as $command) {
            if ($command instanceof ScheduledCommandInterface && $command !== $this) {
                $availableCommands[] = $command;
            }
        }

        $poolSize = $input->getOption('poolSize') ?: $this->config->poolSize;

        do {
            $availableSlots = $poolSize - count($this->getRunningCommands($availableCommands));

            if ($availableSlots > 0) {
                $lockedCommands = $this->getLockedCommands($availableCommands);
                if ($commandList = $this->schedule->findJobsToBeStarted($availableSlots, $lockedCommands)) {
                    foreach ($commandList as $command) {
                        $this->exec($this->config->cmdStart, $command);
                    }
                }
            }
            usleep($this->config->tickInterval);
        } while (!$this->isInterrupted());

        if ($this->isInterrupted()) {
            $output->writeln('Stopping jobs');

            foreach ($this->getRunningCommands($availableCommands) as $commandName) {
                if ($pid = $this->pidService->getPid($this->getApplication()->get($commandName))) {
                    $this->exec($this->config->cmdKill, $pid);
                }
            }

            while (count($this->getRunningCommands($availableCommands)) > 0) {
                sleep(1);
                $output->write('.');
            }
            $output->writeln('.');
        }
    }

    /**
     * @param ScheduledCommandInterface[] $availableCommands
     * @return string[]
     */
    private function getRunningCommands($availableCommands)
    {
        $commands = [];
        foreach ($availableCommands as $command) {
            if ($this->pidService->getPid($command)) {
                $commands[] = $command->getName();
            }
        }
        return $commands;
    }

    /**
     * @param ScheduledCommandInterface[] $availableCommands
     * @return string[]
     */
    private function getLockedCommands($availableCommands)
    {
        $commands = [];
        foreach ($availableCommands as $command) {
            if ($command->isLockedExecution()) {
                $commands[] = $command->getName();
            }
        }
        return $commands;
    }

    private function exec($cmdTemplate, $argument)
    {
        $cmd = sprintf($cmdTemplate, $argument);

        $this->logger->notice(sprintf('Exec: <info>%s</info>', $cmd));

        exec($cmd);
    }
}
