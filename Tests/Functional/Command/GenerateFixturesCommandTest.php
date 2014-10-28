<?php

namespace Hautelook\AliceBundle\Tests\Functional\Command;

use Hautelook\AliceBundle\Command\GenerateFixturesCommand;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateFixturesCommandTest extends AbstractCommandTest
{
    public function testGenerateFixtures()
    {
        $command = $this->application->find('hautelook:alice:generate');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array('entityName' => 'TestBundle:Product'),
            array('interactive' => false)
        );

        $display = $commandTester->getDisplay();

        $expectedOutput = sprintf(
            "> 10 Entities found\n%s\n> Done\n",
            file_get_contents(__DIR__ . '/../GeneratedFixtures/product.yml')
        );

        $this->assertEquals($expectedOutput, $display);
    }

    protected function getCommands()
    {
        return array(new GenerateFixturesCommand());
    }
}
