<?php

namespace DoctrineDatatable;

/**
 * Class HavingColumn.
 *
 * @author Mathieu Petrini <mathieupetrini@gmail.com>
 */
class HavingColumn extends Column
{
    /**
     * @return bool
     */
    public function isHaving(): bool
    {
        return true;
    }
}
