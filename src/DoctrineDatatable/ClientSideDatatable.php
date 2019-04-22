<?php

namespace DoctrineDatatable;

/**
 * Class ServerSideDatatable.
 *
 * @author Mathieu Petrini <mathieupetrini@gmail.com>
 */
class ClientSideDatatable extends Datatable
{
    /**
     * PUBLIC METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param mixed[] $filters
     *
     * @return mixed[]
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\UnfilterableColumn
     * @throws Exception\WhereColumnNotHandle
     */
    public function get(array $filters): array
    {
        $this->createQueryResult();
        $this->createFoundationQuery($filters);

        return array(
            'data' => $this->data($filters),
        );
    }
}
