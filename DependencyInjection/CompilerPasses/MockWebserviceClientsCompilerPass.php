<?php

namespace Smartbox\Integration\FrameworkBundle\DependencyInjection\CompilerPasses;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MockWebserviceClientsCompilerPass.
 */
class MockWebserviceClientsCompilerPass implements CompilerPassInterface
{
    const TAG_MOCKABLE_SOAP_CLIENT = 'mockable.soap_client';
    const TAG_MOCKABLE_REST_CLIENT = 'mockable.rest_client';
    const TAG_ATTR_MOCK_LOCATION = 'mockLocation';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $soapClientIds = $container->findTaggedServiceIds(self::TAG_MOCKABLE_SOAP_CLIENT);

        foreach ($soapClientIds as $id => $parameters) {
            $mockLocation = $parameters[0][self::TAG_ATTR_MOCK_LOCATION];
            $serviceDef = $container->getDefinition($id);
            $serviceDef->setClass($container->getParameter('fake_soap_client.class'));

            $fileLocator = new Reference('file_locator');

            $arguments = $serviceDef->getArguments();
            $arguments[1] = array_merge($arguments[1],
                [
                    'file_locator' => $fileLocator,
                    'cache_dir' => $mockLocation,
                    'cache_exclusions' => [],
                ]
            );
            $serviceDef->setArguments($arguments);
        }

        $restProducerIds = $container->findTaggedServiceIds(self::TAG_MOCKABLE_REST_CLIENT);

        foreach ($restProducerIds as $id => $parameters) {
            $mockLocation = $parameters[0][self::TAG_ATTR_MOCK_LOCATION];
            $serviceDef = $container->getDefinition($id);
            $serviceDef->setClass($container->getParameter('fake_rest_client.class'));
            $serviceDef->addMethodCall('init', [new Reference('file_locator'), $mockLocation, []]);
        }
    }
}
