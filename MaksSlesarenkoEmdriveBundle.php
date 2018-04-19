<?php

namespace MaksSlesarenko\EmdriveBundle;

use MaksSlesarenko\EmdriveBundle\DependencyInjection\LockPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MaksSlesarenkoEmdriveBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
