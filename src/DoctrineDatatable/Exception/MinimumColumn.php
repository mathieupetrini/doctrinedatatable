<?php

namespace DoctrineDatatable\Exception;

/**
 * Class MinimumColumn.
 *
 * @codeCoverageIgnore
 */
class MinimumColumn extends \Exception
{
    public function __construct($code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            'At least one column is mandatory to initialize a datatable',
            $code,
            $previous
        );
    }
}
