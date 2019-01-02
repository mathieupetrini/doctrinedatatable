<?php

namespace DoctrineDatatable\Exception;

/**
 * Class UnfilterableColumn.
 *
 * @author Mathieu Petrini <mathieupetrini@gmail.com>
 *
 * @codeCoverageIgnore
 */
class UnfilterableColumn extends \Exception
{
    /**
     * UnfilterableColumn constructor.
     *
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            'This column cannot be filtered.',
            $code,
            $previous
        );
    }
}
