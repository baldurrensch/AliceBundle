<?php

namespace Hautelook\AliceBundle\Generator;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface that all adapter have to implement
 *
 * @author Baldur Rensch <brensch@gmail.com>
 */
interface AdapterInterface
{
    /**
     * Queries the adapter for the entities
     *
     * @param string $entityName
     * @param string $query
     * @return mixed
     */
    public function getEntities($entityName, $query = null);

    /**
     * Provides a unique identifier for the entity
     *
     * @param mixed $entity
     * @return string
     */
    public function generateIdentifier($entity);

    /**
     * Generates fixtures representing the entity
     *
     * @param mixed $entity
     * @return Fixture
     */
    public function generateFixture($entity);

    /**
     * Retrieves all the generated fixtures
     *
     * @return ArrayCollection
     */
    public function getFixtures();
} 
