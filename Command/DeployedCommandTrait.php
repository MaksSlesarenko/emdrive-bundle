<?php

namespace MaksSlesarenko\EmdriveBundle\Command;

/**
 * Trait DeployedCommandTrait
 *
 * @package MaksSlesarenko\EmdriveBundle\Command
 */
trait DeployedCommandTrait
{
    public function getDeployPriority()
    {
        return 0;
    }
}