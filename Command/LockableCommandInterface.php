<?php

namespace EmdriveBundle\Command;

/**
 * Interface LockableCommandInterface
 *
 * @mixin \Symfony\Component\Console\Command\Command
 *
 * @package EmdriveBundle\Command
 */
interface LockableCommandInterface
{
    public function lock($blocking = false);

    public function isLockedExecution();

    public function unlock();

    public function getLockName();
}
