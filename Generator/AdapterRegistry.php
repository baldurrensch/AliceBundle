<?php

namespace Hautelook\AliceBundle\Generator;

class AdapterRegistry
{
    private $adapters;

    public function __construct(array $adapters = array())
    {
        foreach ($adapters as $adapter) {
            if (!$adapter instanceof AdapterInterface) {
                throw new \InvalidArgumentException(
                    'Adapters need to implement the \Hautelook\AliceBundle\Generator' .
                    '\AdapterInterface interface'
                );
            }
        }

        $this->adapters = $adapters;
    }

    public function hasAdapter($key)
    {
        return !empty($this->adapters[$key]);
    }

    /**
     * @param $key
     * @return AdapterInterface
     */
    public function getAdapter($key)
    {
        return $this->adapters[$key];
    }
}
