<?php

namespace Hautelook\AliceBundle\Generator;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class Fixture
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @param string $class
     * @param string $identifier
     */
    public function __construct($class, $identifier)
    {
        $this->className = $class;
        $this->identifier = $identifier;
        $this->fields = array();
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function addField($name, $value)
    {
        if (is_numeric($value)) {
            if ($this->isWholeNumber($value)) {
                $value = (int) $value;
            } else {
                $value = (float) $value;
            }
        }

        $this->fields[$name] = $value;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function isWholeNumber($var)
    {
        return (is_numeric($var) && (intval($var) == floatval($var)));
    }
}
