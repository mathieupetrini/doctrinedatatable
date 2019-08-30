<?php

declare(strict_types=1);

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
     * @throws ReflectionException
     */
    public static function callMethod($obj, string $name, array $args = array())
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs(is_object($obj) ? $obj : null, $args);
    }
}
