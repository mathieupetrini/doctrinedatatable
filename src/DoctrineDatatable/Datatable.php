<?php

namespace DoctrineDatatable;

use Doctrine\ORM\QueryBuilder;

class Datatable
{
    /**
     * @var QueryBuilder
     */
    private $query;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var Column[]
     */
    private $columns;

    /**
     * @var int
     */
    private $result_per_page;

    /**
     * @var int
     */
    public const RESULT_PER_PAGE = 30;

    public function __construct(
        QueryBuilder $query,
        string $identifier,
        array $columns,
        ?int $result_per_page = self::RESULT_PER_PAGE
    ) {
        $this->query = $query;
        $this->identifier = $identifier;
        $this->columns = $columns;
        $this->result_per_page = $result_per_page ?? self::RESULT_PER_PAGE;
    }

    /**
     * PRIVATE METHODS.
     */

    /**
     * @param string $alias
     *
     * @return Column|null
     */
    private function getColumnFromAlias(string $alias): ?Column
    {
        $index = array_search(
            $alias,
            // Aliases in an array
            array_column(
                $this->columns,
                'alias',
                'alias'
            )
        );

        return false !== $index ?
            $this->columns[$index] :
            null;
    }

    private function createFoundationQuery(QueryBuilder &$query, array $filtres): QueryBuilder
    {
        foreach ($filtres as $alias => $filtre) {
            if (!empty($filtre)) {
                $column = $this->getColumnFromAlias($alias);
                if ($column instanceof Column) {
                    $column->where($query, $filtre);
                }
            }
        }
    }

    /**
     * @return QueryBuilder
     */
    private function createQueryResult(): QueryBuilder
    {
        $query = clone $this->query;
        $query->select($this->identifier);
        foreach ($this->columns as $alias => $column) {
            $this->processColumnSelect($query, $alias, $column);
        }

        return $query;
    }

    private function processColumnSelect(QueryBuilder &$query, string $alias, Column $column): void
    {
        $query->addSelect($column->getName().' AS '.$alias);
    }

    private function orderBy(QueryBuilder &$query, int $index, string $direction): self
    {
        $query->orderBy(
            \array_slice($this->columns, $index, 1)['alias'],
            $direction
        );

        return $this;
    }

    private function limit(QueryBuilder &$query, int $start): self
    {
        $query->setFirstResult($start)
            ->setMaxResults($this->result_per_page);
    }

    /**
     * PUBLIC METHODS.
     */
    public function result(
        int $index,
        string $direction,
        int $start
    ): array {
        $query = $this->createQueryResult();

        $this->orderBy($query, $index, $direction)
            ->limit($query, $start);

        return $query->getQuery()
            ->getResult();
    }

    public function count(): int
    {
        $query = clone $this->query;

        return (int) ($query->select('COUNT(DISTINCT '.$this->identifier.')')
            ->getQuery()
            ->getSingleScalarResult());
    }

    public function get(
        array $filtres,
        int $index,
        string $direction,
        int $start
    ): array {
        $this->createFoundationQuery($filtres);
        $data = $this->result($index, $direction, $start);

        return array(
            'recordsTotal' => $this->count(),
            'recordsFiltered' => \count($data),
            'data' => $data,
        );
    }
}
