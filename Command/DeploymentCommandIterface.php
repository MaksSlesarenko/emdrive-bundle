<?php

namespace EmdriveBundle\Command;

/**
 * Interface DeploymentCommandIterface
 *
 * @mixin \Symfony\Component\Console\Command\Command
 *
 * @package EmdriveBundle\Command
 */
interface DeploymentCommandIterface
{
    /**
     * Get deploy priority
     * Higher gets first
     * @return int
     */
    public function getDeployPriority();
}