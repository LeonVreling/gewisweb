<?php

namespace Frontpage\Controller\Factory;

use Frontpage\Controller\InfimumController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class InfimumControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     *
     * @return InfimumController
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): InfimumController {
        return new InfimumController(
            $container->get('application_service_infimum'),
            $container->get('frontpage_service_acl'),
            $container->get('translator'),
        );
    }
}