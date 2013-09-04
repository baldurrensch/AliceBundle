<?php

namespace Hautelook\AliceBundle\Tests\Alice;

use Hautelook\AliceBundle\Alice\Loader;
use Mockery\MockInterface;

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockInterface|\Psr\Log\LoggerInterface
     */
    public $log;

    /**
     * @var MockInterface
     */
    public $ymlLoader;

    /**
     * @var array
     */
    public $loaders;

    /**
     * @var MockInterface|\Doctrine\Common\Persistence\ObjectManager
     */
    public $objectManager;

    public function testLoadNoFiles()
    {
        $loader = new Loader($this->loaders, $this->log);

        $this->ymlLoader
            ->shouldReceive('load')
            ->never();

        $this->ymlLoader
            ->shouldReceive('getReferences')
            ->andReturn(array());

        $this->ymlLoader
            ->shouldReceive('setProviders');


        $loader->load(array());
    }

    public function testLoadCalledForEachFile()
    {
        $filesToLoad = array(
            'file_1',
            'file_2',
        );

        $fileResults = array(
            array(1),
            array(2),
        );

        $this->setupYmlLoader($filesToLoad, $fileResults);

        $this->objectManager
            ->shouldReceive('persist')
            ->andReturn($fileResults);

        $this->objectManager
            ->shouldReceive('flush');

        $loader = new Loader($this->loaders, $this->log);
        $loader->setObjectManager($this->objectManager);

        $loader->load($filesToLoad);
    }

    public function testReferencesDetached()
    {
        $filesToLoad = array(
            'file_1',
            'file_2',
        );

        $fileResults = array(
            array(1),
            array(2),
        );

        $this->setupYmlLoader($filesToLoad, $fileResults);

        $a = new Obj();
        $a->value = 'a';

        $b = new Obj();
        $b->value = 'b';

        $this->ymlLoader
            ->shouldReceive('getReferences')
            ->andReturn(array('a' => $a, 'b' => $b));

        $this->objectManager
            ->shouldReceive('persist')
            ->andReturn($fileResults);

        $this->objectManager
            ->shouldReceive('detach')
            ->with($a);

        $this->objectManager
            ->shouldReceive('detach')
            ->with($b);

        $this->objectManager
            ->shouldReceive('flush');

        $loader = new Loader($this->loaders, $this->log);

        $loader->setObjectManager($this->objectManager);

        $loader->load($filesToLoad);
    }

    protected function setUp()
    {
        $this->log = \Mockery::mock('Psr\Log\LoggerInterface');

        $this->ymlLoader = \Mockery::mock('Nelmio\Alice\Loader\Yaml');

        $this->loaders = array(
            'yaml' => $this->ymlLoader,
        );

        $this->objectManager = \Mockery::mock('Doctrine\Common\Persistence\ObjectManager');
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @param array $filesToLoad
     * @param array $fileResults
     */
    protected function setupYmlLoader(array $filesToLoad, array $fileResults)
    {
        for ($i = 0; $i < count($filesToLoad); $i++) {
            $this->ymlLoader
                ->shouldReceive('load')
                ->with($filesToLoad[$i])
                ->andReturn($fileResults[$i]);
        }

        $this->ymlLoader
            ->shouldReceive('getReferences')
            ->andReturn(array());

        $this->ymlLoader
            ->shouldReceive('setLogger')
            ->with($this->log);

        $this->ymlLoader
            ->shouldReceive('setORM', 'setReferences', 'setProviders');
    }
}

class Obj
{
    public $value;
}
