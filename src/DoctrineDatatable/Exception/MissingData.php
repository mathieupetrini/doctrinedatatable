<?php

namespace DoctrineDatatable\Exception;

class MissingData extends \Exception
{
    /**
     * MissingData constructor.
     *
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            'Data parameters not found.',
            $code,
            $previous
        );
    }
}
