<?php

namespace EmdriveBundle\Command;

/**
 * Trait DeployedCommandTrait
 *
 * @package EmdriveBundle\Command
 */
trait DeployedCommandTrait
{
    public function getDeployPriority()
    {
        return 0;
    }
}