<?php

namespace DoctrineDatatable\Exception;

/**
 * Class MinimumColumn.
 *
 * @author Mathieu Petrini <mathieupetrini@gmail.com>
 *
 * @codeCoverageIgnore
 */
class MinimumColumn extends \Exception
{
    /**
     * MinimumColumn constructor.
     *
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            'At least one column is mandatory to initialize a datatable',
            $code,
            $previous
        );
    }
}
