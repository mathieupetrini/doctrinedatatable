<?php

namespace DoctrineDatatable;

use Doctrine\ORM\Query;

class Export
{
    /**
     * @var Datatable
     */
    private $datatable;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @var string
     */
    private $delimiter;

    public function __construct()
    {
        $this->setDelimiter("\t");
    }

    /**
     * PRIVATE METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     */
    private function data(): void
    {
        foreach ($this->datatable->createFinalQuery(array(
            'start' => 0,
            'limit' => INF, ))->getQuery()->iterate(null, Query::HYDRATE_SCALAR) as $row) {
            $line = $row[0];
            fwrite($this->resource, implode($this->delimiter, $line));
        }
        rewind($this->resource);
    }

    /**
     * PUBLIC METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @return resource
     */
    public function export()
    {
        $this->resource = tmpfile();
        $this->data();

        return $this->resource;
    }

    /**
     * GETTERS / SETTERS.
     */

    /**
     * @param Datatable $datatable
     *
     * @return Export
     */
    public function setDatatable(Datatable $datatable): self
    {
        $this->datatable = $datatable;

        return $this;
    }

    /**
     * @param string $delimiter
     *
     * @return Export
     */
    public function setDelimiter(string $delimiter): self
    {
        $this->delimiter = $delimiter;

        return $this;
    }
}
