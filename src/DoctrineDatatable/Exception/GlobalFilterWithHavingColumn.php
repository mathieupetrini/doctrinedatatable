<?php

namespace DoctrineDatatable\Exception;

class GlobalFilterWithHavingColumn extends \Exception
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
            'Global filter is not available with HavingColumn',
            $code,
            $previous
        );
    }
}
