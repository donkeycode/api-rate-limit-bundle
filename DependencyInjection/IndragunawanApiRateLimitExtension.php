<?php

/*
 * This file is part of the ApiRateLimitBundle
 *
 * (c) Indra Gunawan <hello@indra.my.id>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indragunawan\ApiRateLimitBundle\DependencyInjection;

use Doctrine\Common\Cache\FilesystemCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * The extension of this bundle.
 *
 * @author Indra Gunawan <hello@indra.my.id>
 */
final class IndragunawanApiRateLimitExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->registerEventListenerConfig($container, $config);
        $this->registerServiceConfig($container, $config);
    }

    private function registerEventListenerConfig(ContainerBuilder $container, array $config)
    {
        $container->getDefinition('indragunawan_api_rate_limit.event_listener.rate_limit')
            ->replaceArgument(0, $config['enabled'])
            ->replaceArgument(2, $config['exception']);

        $container->getDefinition('indragunawan_api_rate_limit.event_listener.header_modification')
            ->replaceArgument(0, $config['header']);
    }

    private function registerServiceConfig(ContainerBuilder $container, array $config)
    {
        if (null === $config['storage']) {
            $storage = new Definition(FilesystemCache::class, [$container->getParameter('kernel.cache_dir').'/rate_limit']);
        } else {
            $storage = new Reference($config['storage']);
        }

        $container->getDefinition('indragunawan_api_rate_limit.service.rate_limit_handler')
            ->replaceArgument(0, $storage)
            ->replaceArgument(1, $config['throttle']);
    }
}
