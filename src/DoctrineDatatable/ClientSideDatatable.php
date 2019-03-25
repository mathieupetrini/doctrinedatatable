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
        $query = $this->createQueryResult();
        $this->createFoundationQuery($query, $filters);

        $data = $this->result(
            $query,
            isset($filters['order']) ?
                $filters['order'][0]['column'] :
                0,
            isset($filters['order']) ?
                $filters['order'][0]['dir'] :
                'ASC'
        );

        return array(
            'data' => $data,
        );
    }
}
