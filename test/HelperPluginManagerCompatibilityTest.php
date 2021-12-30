<?php

declare(strict_types=1);

namespace LaminasTest\View;

use Generator;
use Laminas\Mvc\Controller\Plugin\FlashMessenger as V2FlashMessenger;
use Laminas\Mvc\Controller\PluginManager as ControllerPluginManager;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\View\Exception\InvalidHelperException;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function class_exists;
use function strpos;

class HelperPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager(): HelperPluginManager
    {
        $factories = [];

        if (class_exists(ControllerPluginManager::class)) {
            // @codingStandardsIgnoreLine
            $factories['ControllerPluginManager'] = function ($services, $name, $options): \Laminas\Mvc\Controller\PluginManager {
                return new ControllerPluginManager($services, [
                    'invokables' => [
                        'flashmessenger' => class_exists(FlashMessenger::class)
                            ? FlashMessenger::class
                            : V2FlashMessenger::class,
                    ],
                ]);
            };
        }

        $config  = new Config([
            'services'  => [
                'config' => [],
            ],
            'factories' => $factories,
        ]);
        $manager = new ServiceManager();
        $config->configureServiceManager($manager);
        return new HelperPluginManager($manager);
    }

    protected function getV2InvalidPluginException(): string
    {
        return InvalidHelperException::class;
    }

    /**
     * @psalm-return Generator<mixed, array{0: mixed, 1: mixed}, mixed, void>
     */
    public function aliasProvider(): Generator
    {
        $pluginManager = $this->getPluginManager();
        $r             = new ReflectionProperty($pluginManager, 'aliases');
        $r->setAccessible(true);
        $aliases = $r->getValue($pluginManager);

        foreach ($aliases as $alias => $target) {
            // Skipping conditionally since it depends on laminas-mvc
            if (! class_exists(ControllerPluginManager::class) && strpos($target, '\\FlashMessenger')) {
                continue;
            }

            // Skipping conditionally since it depends on laminas-mvc
            if (! class_exists(ControllerPluginManager::class) && strpos($target, '\\Url')) {
                continue;
            }

            yield $alias => [$alias, $target];
        }
    }

    public function getInstanceOf(): void
    {
        // no-op; instanceof is not used in this implementation
    }

    public function testInstanceOfMatches(): void
    {
        $this->markTestSkipped('instanceOf is not used with this implementation');
    }

    /**
     * @todo remove this test once we set the minimum laminas-servicemanager version to 3
     */
    public function testRegisteringInvalidElementRaisesException(): void
    {
        $this->expectException($this->getServiceNotFoundException());
        $this->getPluginManager()->setService('test', $this);
    }

    /**
     * @todo remove this test once we set the minimum laminas-servicemanager version to 3
     */
    public function testLoadingInvalidElementRaisesException(): void
    {
        $manager = $this->getPluginManager();
        $manager->setInvokableClass('test', static::class);
        $this->expectException($this->getServiceNotFoundException());
        $manager->get('test');
    }
}
