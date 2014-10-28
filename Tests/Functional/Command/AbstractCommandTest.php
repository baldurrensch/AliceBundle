<?php

namespace Hautelook\AliceBundle\Tests\Functional\Command;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Hautelook\AliceBundle\Tests\Functional\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCommandTest extends TestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var string
     */
    private $fixtureDisplay;

    protected function setUp()
    {
        parent::setUp();

        $this->application = new Application(TestCase::getKernel());

        $commands = array_merge(
            array(
                new LoadDataFixturesDoctrineCommand(),
                new CreateSchemaDoctrineCommand()
            ),
            $this->getCommands()
        );
        $this->application->addCommands($commands);

        $this->createSchema();
        $this->loadFixtures();
    }

    protected function loadFixtures()
    {
        $command = $this->application->find('doctrine:fixtures:load');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(), array('interactive' => false));

        $this->fixtureDisplay = $commandTester->getDisplay();
    }

    protected function createSchema()
    {
        $command = $this->application->find('doctrine:schema:create');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array());
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected function getDoctrine()
    {
        return $this->application->getKernel()->getContainer()->get('doctrine');
    }

    /**
     * @return Command[]
     */
    abstract protected function getCommands();

    /**
     * @return string
     */
    protected function getFixtureDisplay()
    {
        return $this->fixtureDisplay;
    }
}
