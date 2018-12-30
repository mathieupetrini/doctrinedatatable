<?php

namespace DoctrineDatatable;

use Doctrine\ORM\QueryBuilder;
use DoctrineDatatable\Exception\MinimumColumn;

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

    /**
     * Datatable constructor.
     *
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param string       $identifier
     * @param array        $columns
     * @param int|null     $result_per_page
     *
     * @throws MinimumColumn
     */
    public function __construct(
        QueryBuilder $query,
        string $identifier,
        array $columns,
        ?int $result_per_page = self::RESULT_PER_PAGE
    ) {
        if (empty($columns)) {
            throw new MinimumColumn();
        }
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
            array_keys(
                array_column($this->columns, 'alias', 'alias')
            )
        );

        return false !== $index ?
            $this->columns[$index] :
            null;
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param array        $filtres
     *
     * @return QueryBuilder
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\WhereColumnNotHandle
     */
    private function createFoundationQuery(QueryBuilder &$query, array $filtres): QueryBuilder
    {
        foreach ($filtres as $alias => $filtre) {
            $column = $this->getColumnFromAlias($alias);
            if ($column instanceof Column && !empty($filtre)) {
                $column->where($query, $filtre);
            }
        }

        return $query;
    }

    /**
     * @return QueryBuilder
     */
    private function createQueryResult(): QueryBuilder
    {
        $query = clone $this->query;
        $query->select($this->processColumnIdentifier($query));
        foreach ($this->columns as $column) {
            $this->processColumnSelect($query, $column);
        }

        return $query;
    }

    private function processColumnIdentifier(QueryBuilder &$query): string
    {
        return $query->getRootAliases()[0].'.'.$this->identifier;
    }

    private function processColumnSelect(QueryBuilder &$query, Column $column): void
    {
        $query->addSelect($column->getName().' AS '.$column->getAlias());
    }

    private function orderBy(QueryBuilder &$query, int $index, string $direction): self
    {
        $query->orderBy(
            \array_slice($this->columns, $index, 1)[0]->getAlias(),
            $direction
        );

        return $this;
    }

    /**
     * @param QueryBuilder $query
     * @param int          $start
     *
     * @return Datatable
     */
    private function limit(QueryBuilder &$query, int $start): self
    {
        $query->setFirstResult($start)
            ->setMaxResults($this->result_per_page);

        return $this;
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param int          $index
     * @param string       $direction
     * @param int          $start
     *
     * @return array
     */
    private function result(
        QueryBuilder &$query,
        int $index,
        string $direction,
        int $start
    ): array {
        $this->orderBy($query, $index, $direction)
            ->limit($query, $start);

        return $query->getQuery()
            ->getResult();
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function count(QueryBuilder $query): int
    {
        $query = clone $query;

        return (int) ($query->select('COUNT(DISTINCT '.$this->processColumnIdentifier($query).')')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult());
    }

    /**
     * PUBLIC METHODS.
     */

    /**
     * @param array  $filtres
     * @param int    $index (optional) (default=0)
     * @param string $direction (optional) (default='ASC')
     * @param int    $start (optional) (default=0)
     *
     * @return array
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\WhereColumnNotHandle
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get(
        array $filtres,
        int $index = 0,
        string $direction = 'ASC',
        int $start = 0
    ): array {
        $query = $this->createQueryResult();
        $this->createFoundationQuery($query, $filtres);

        $data = $this->result($query, $index, $direction, $start);

        return array(
            'recordsTotal' => $this->count($query),
            'recordsFiltered' => \count($data),
            'data' => $data,
        );
    }
}
