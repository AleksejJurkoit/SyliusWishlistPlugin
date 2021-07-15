<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\DependencyInjection;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class BitBagSyliusWishlistExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    public function load(array $config, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $config);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $this->registerResources('bitbag_sylius_wishlist_plugin', 'doctrine/orm', $config['resources'], $container);
        $loader->load('services.yml');
        $container->setParameter('wishlist_cookie_token', $config['wishlist_cookie_token']);
    }

    public function prepend(ContainerBuilder $container): void
    {
        trigger_deprecation('bitbag/wishlist-plugin', '2.0', 'Doctrine migrations existing in a bundle will be removed, move migrations to the project directory.');
        $this->prependDoctrineMigrations($container);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'BitBag\SyliusWishlistPlugin\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@BitBagSyliusWishlistPlugin/Migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return ['Sylius\Bundle\CoreBundle\Migrations'];
    }

    private function prependDoctrineMigrations(ContainerBuilder $container): void
    {
        if (
            !$container->hasExtension('doctrine_migrations') ||
            !$container->hasExtension('sylius_labs_doctrine_migrations_extra')
        ) {
            return;
        }

        if (
            $container->hasParameter('sylius_core.prepend_doctrine_migrations') &&
            !$container->getParameter('sylius_core.prepend_doctrine_migrations')
        ) {
            return;
        }

        $doctrineConfig = $container->getExtensionConfig('doctrine_migrations');
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => \array_merge(\array_pop($doctrineConfig)['migrations_paths'] ?? [], [
                $this->getMigrationsNamespace() => $this->getMigrationsDirectory(),
            ]),
        ]);

        $container->prependExtensionConfig('sylius_labs_doctrine_migrations_extra', [
            'migrations' => [
                $this->getMigrationsNamespace() => $this->getNamespacesOfMigrationsExecutedBefore(),
            ],
        ]);
    }
}
