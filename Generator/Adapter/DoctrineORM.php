<?php

namespace Hautelook\AliceBundle\Generator\Adapter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;
use Hautelook\AliceBundle\Generator\AdapterInterface;
use Hautelook\AliceBundle\Generator\Fixture;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class DoctrineORM implements AdapterInterface
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var ArrayCollection
     */
    private $fixtures;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->fixtures = new ArrayCollection();
    }

    public function getEntities($entityName, $query = null)
    {
        return $this->doctrine->getRepository($entityName)->findAll();
    }

    public function generateIdentifier($entity)
    {

        $metadata = $this->getMetadata($entity);

        $identifiers = array_map(
            function ($identifier) use ($metadata, $entity) {
                return $metadata->getFieldValue($entity, $identifier);
            },
            $metadata->getIdentifier()
        );

        $identifier = sprintf('%s_%s', $metadata->getName(), strtolower(implode('_', $identifiers)));

        return $identifier;
    }

    public function generateFixture($entity)
    {
        $fields = $this->getMetadata($entity)->getFieldNames();

        $fixture = new Fixture(
            $this->getMetadata($entity)->getName(),
            $this->generateIdentifier($entity)
        );

        foreach ($fields as $field) {
            $fixture->addField($field, $this->getMetadata($entity)->getFieldValue($entity, $field));
        }

        $this->fixtures->set(spl_object_hash($entity), $fixture);

        foreach ($this->getMetadata($entity)->getAssociationMappings() as $associationMapping) {
            $associationFieldName = $associationMapping['fieldName'];

            // Look up association
            $getter = 'get' . ucfirst($associationFieldName);

            $refl = new \ReflectionClass($associationMapping['sourceEntity']);
            if ($refl->hasMethod($getter)) {
                $associatedEntity = $entity->$getter();

                $rels = null;
                if (is_array($associatedEntity) || $associatedEntity instanceof Collection) {
                    foreach ($associatedEntity as $elem) {
                        $newFixture = $this->generateFixtureForAssociation($elem);
                        $rels[] = sprintf('@%s', $newFixture->getIdentifier());
                    }
                } else {
                    $newFixture = $this->generateFixtureForAssociation($associatedEntity);
                    $rels = sprintf('@%s', $newFixture->getIdentifier());
                }

                $fixture->addField($associationFieldName, $rels);
                $this->fixtures->set(spl_object_hash($entity), $fixture);
            }
        }

        return $fixture;
    }

    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return $this->fixtures;
    }

    private function getMetadata($entity)
    {
        /** @var $metadata ClassMetadata */
        $metadata = $this->doctrine->getManager()->getClassMetadata(get_class($entity));

        return $metadata;
    }

    /**
     * @param $associatedEntity
     * @return Fixture
     */
    protected function generateFixtureForAssociation($associatedEntity)
    {
        if (!$this->fixtures->containsKey($key = spl_object_hash($associatedEntity))) {
            $this->fixtures->set($key, $this->generateFixture($associatedEntity));
        }

        return $this->fixtures->get($key);
    }
}
