<?php

namespace Smartbox\Integration\FrameworkBundle;

use Smartbox\Integration\FrameworkBundle\DependencyInjection\ConnectorsCompilerPass;
use Smartbox\Integration\FrameworkBundle\DependencyInjection\EventDeferringCompilerPass;
use Smartbox\Integration\FrameworkBundle\DependencyInjection\MockWebserviceClientsCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SmartboxIntegrationFrameworkBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EventDeferringCompilerPass(),PassConfig::TYPE_AFTER_REMOVING);

        if($container->getParameter('kernel.environment') == 'test'){
            $container->addCompilerPass(new MockWebserviceClientsCompilerPass(),PassConfig::TYPE_AFTER_REMOVING);
        }
    }
}
