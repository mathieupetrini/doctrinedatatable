<?php

declare(strict_types=1);

namespace DoctrineDatatable;

/**
 * Class Tools.
 */
class Tools
{
    /**
     * @param mixed  $obj
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function callMethod($obj, string $name, array $args = array())
    {
        $method = (new \ReflectionClass($obj))->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs(is_object($obj) ? $obj : null, $args);
    }
}
