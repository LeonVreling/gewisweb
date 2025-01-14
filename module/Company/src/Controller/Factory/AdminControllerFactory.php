<?php

namespace Company\Controller\Factory;

use Company\Controller\AdminController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AdminControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return AdminController
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): AdminController {
        return new AdminController(
            $container->get('company_service_acl'),
            $container->get('translator'),
            $container->get('company_service_company'),
            $container->get('company_service_companyquery'),
        );
    }
}
