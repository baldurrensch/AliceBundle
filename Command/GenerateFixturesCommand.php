<?php

namespace Hautelook\AliceBundle\Command;

use Doctrine\Common\Collections\Collection;
use Hautelook\AliceBundle\Generator\AdapterInterface;
use Hautelook\AliceBundle\Generator\Fixture;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Dumper;

class GenerateFixturesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hautelook:alice:generate')
            ->setDescription('Generate data fixtures from your database.')
            ->addArgument('entityName', InputArgument::REQUIRED, 'The name of the entity to query')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'The directory of the output files')
            ->addOption('adapter', null, InputOption::VALUE_OPTIONAL, 'The name of the adapter to use', 'orm')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityName = $input->getArgument('entityName');
        $outputDirectory = $input->getOption('output') ? sprintf('%s/%s', __DIR__, $input->getOption('output')) : null;
        $type = $input->getOption('adapter');

        if (!empty($outputDirectory) && !is_writable($outputDirectory)) {
            throw new \RuntimeException(sprintf('Cannot open file %s for writing', $outputDirectory));
        }

        /** @var $adapter AdapterInterface */
        $adapter = $this->getContainer()->get('hautelook_alice.generator.adapter_registry')->getAdapter($type);

        $entities = $adapter->getEntities($entityName);
        $output->writeln(sprintf('> %d Entities found', count($entities)));

        foreach ($entities as $entity) {
            $adapter->generateFixture($entity);
        }

        $fixtures = $this->splitFixturesByEntity($adapter->getFixtures());

        $this->outputFixtures($output, $fixtures, $outputDirectory);

        $output->writeln('> Done');
    }

    /**
     * @param Collection $fixtures
     * @return array
     */
    protected function splitFixturesByEntity(Collection $fixtures)
    {
        $fixturesByEntity = array();

        /** @var $fixture Fixture */
        foreach ($fixtures as $fixture) {
            if (empty($fixturesByEntity[$fixture->getClassName()])) {
                $fixturesByEntity[$fixture->getClassName()] = array($fixture);
            } else {
                $fixturesByEntity[$fixture->getClassName()][] = $fixture;
            }
        }

        return $fixturesByEntity;
    }

    /**
     * @param OutputInterface $output
     * @param array $fixturesByEntity
     * @param string|null $outputDirectory
     */
    protected function outputFixtures(OutputInterface $output, $fixturesByEntity, $outputDirectory = null)
    {
        $dumper = new Dumper();

        foreach ($fixturesByEntity as $entityName => $fixtures) {
            $fixtureData = array();
            /** @var $fixture Fixture */
            foreach ($fixtures as $fixture) {
                $fixtureData[$fixture->getIdentifier()] = $fixture->getFields();
            }

            $yaml = $dumper->dump(array($entityName => $fixtureData), 3);

            if ($outputDirectory) {
                file_put_contents($fileName = sprintf('%s/%s.yml', $outputDirectory, $this->generateFileName($entityName)), $yaml);
                $output->writeln(sprintf('Fixture file created: %s', $fileName));
            } else {
                $output->writeln($yaml);
            }
        }
    }

    private function generateFileName($entityName)
    {
        return strtolower(substr($entityName, strrpos($entityName, '\\') + 1));
    }
}
