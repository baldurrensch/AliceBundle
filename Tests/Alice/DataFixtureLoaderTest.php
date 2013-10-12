<?php

namespace Hautelook\AliceBundle\Tests\Alice;

use Hautelook\AliceBundle\Alice\DataFixtureLoader;
use Hautelook\AliceBundle\Alice\Loader;
use Nelmio\Alice\Loader\Yaml;
use Prophecy\PhpUnit\ProphecyTestCase;

class DataFixtureLoaderTest extends ProphecyTestCase
{
    private $container;

    public function testLoading()
    {
        $objectManager = $this->getMockObjectManager();
        $container = $this->getMockContainer();

        $loader = new TestLoader();
        $loader->setContainer($container->reveal());
        $loader->load($objectManager->reveal());
    }

    public function testLoadingTwice()
    {
        $objectManager = $this->getMockObjectManager();

        $container = $this->getMockContainer();

        $loader = new TestLoader();
        $loader->setContainer($container->reveal());

        $loader->load($objectManager->reveal());

//        $loader->load($objectManager->reveal());
    }

    private function getMockObjectManager()
    {
        $ob = $this->prophesize('Doctrine\Common\Persistence\ObjectManager');
//        $ob->persist()->shouldBeCalled();
//        $ob->flush()->shouldBeCalled();
//        $ob->detach()->shouldBeCalled();

        return $ob;
    }

    private function getMockContainer()
    {
        if ($this->container) {
            return $this->container;
        }

        $actualLoader = new Loader(
            array(
                'yaml' => new Yaml()
            ),
            null
        );

        $this->container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->get('hautelook_alice.loader')
            ->willReturn($actualLoader);

        return $this->container;
    }
}

class TestLoader extends DataFixtureLoader
{
    /**
     * Returns an array of file paths to fixtures
     *
     * @return array<string>
     */
    protected function getFixtures()
    {
        return array(
            __DIR__ . '/fixture1.yml',
        );
    }
}