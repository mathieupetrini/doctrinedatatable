<?php

namespace DoctrineDatatable;

use Doctrine\ORM\QueryBuilder;
use DoctrineDatatable\Exception\MinimumColumn;

/**
 * Class Datatable.
 *
 * @author Mathieu Petrini <mathieupetrini@gmail.com>
 */
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
    private $resultPerPage;

    /**
     * @var string
     */
    private $nameIdentifier;

    /**
     * @var bool
     */
    private $globalSearch;

    private const DEFAULT_NAME_IDENTIFIER = 'DT_RowID';

    public const RESULT_PER_PAGE = 30;

    /**
     * Datatable constructor.
     *
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param string       $identifier
     * @param array        $columns
     * @param int|null     $resultPerPage
     *
     * @throws MinimumColumn
     */
    public function __construct(
        QueryBuilder $query,
        string $identifier,
        array $columns,
        ?int $resultPerPage = self::RESULT_PER_PAGE
    ) {
        if (empty($columns)) {
            throw new MinimumColumn();
        }
        $this->query = $query;
        $this->identifier = $identifier;
        $this->columns = $columns;
        $this->resultPerPage = $resultPerPage ?? self::RESULT_PER_PAGE;
        $this->nameIdentifier = self::DEFAULT_NAME_IDENTIFIER;
        $this->globalSearch = false;
    }

    /**
     * PRIVATE METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param array $filters
     *
     * @return array
     */
    private function createGlobalFilters(array $filters): array
    {
        $temp = array(
            'columns' => array(),
        );
        array_map(function () use ($filters, &$temp) {
            $temp['columns'][] = array(
                'search' => array(
                    'value' => $filters['search'][Column::GLOBAL_ALIAS],
                ),
            );
        }, $this->columns);

        return $temp;
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param array        $filters
     *
     * @return string
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\UnfilterableColumn
     * @throws Exception\WhereColumnNotHandle
     */
    private function createWherePart(QueryBuilder &$query, array $filters): string
    {
        $temp = '';

        foreach (isset($filters['columns']) ? $filters['columns'] : array() as $index => $filter) {
            if (isset($this->columns[$index]) && !empty($filter['search']['value'])) {
                $temp .= (!empty($temp) ? ' '.($this->globalSearch ? 'OR' : 'AND').' ' : '').
                    '('.$this->columns[$index]->where($query, $filter['search']['value']).')';
            }
        }

        return $temp;
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param array        $filters
     *
     * @return QueryBuilder
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\UnfilterableColumn
     * @throws Exception\WhereColumnNotHandle
     */
    private function createFoundationQuery(QueryBuilder &$query, array $filters): QueryBuilder
    {
        // If global search we erase all specific where and only keep the unified filter
        if ($this->globalSearch && isset($filters['search']) && !empty($filters['search'][Column::GLOBAL_ALIAS])) {
            $filters = $this->createGlobalFilters($filters);
        }

        $temp = $this->createWherePart($query, $filters);

        return !empty($temp) ?
            $query->andWhere($temp) :
            $query;
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

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param bool         $withAlias (optional) (default=true)
     *
     * @return string
     */
    private function processColumnIdentifier(QueryBuilder &$query, bool $withAlias = true): string
    {
        return $query->getRootAliases()[0].'.'.$this->identifier.($withAlias ? ' AS '.$this->nameIdentifier : '');
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param Column       $column
     */
    private function processColumnSelect(QueryBuilder &$query, Column $column): void
    {
        $query->addSelect($column->getName().' AS '.$column->getAlias());
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param int          $index
     * @param string       $direction
     *
     * @return Datatable
     */
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
     * @param int|null     $length
     *
     * @return Datatable
     */
    private function limit(QueryBuilder &$query, int $start, int $length = null): self
    {
        $query->setFirstResult($start)
            ->setMaxResults($length ?? $this->resultPerPage);

        return $this;
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param int          $index
     * @param string       $direction
     *
     * @return array
     */
    private function result(
        QueryBuilder &$query,
        int $index,
        string $direction
    ): array {
        $this->orderBy($query, $index, $direction);

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

        return (int) ($query->select('COUNT(DISTINCT '.$this->processColumnIdentifier($query, false).')')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult());
    }

    /**
     * PUBLIC METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param array    $filters
     * @param int|null $length
     *
     * @return array
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\UnfilterableColumn
     * @throws Exception\WhereColumnNotHandle
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get(
        array $filters,
        int $length = null
    ): array {
        $query = $this->createQueryResult();
        $this->createFoundationQuery($query, $filters);

        $data = $this->limit(
            $query,
            isset($filters['start']) ? $filters['start'] : 0,
            $length ?? (isset($filters['length']) ? $filters['length'] : $this->resultPerPage)
        )->result(
            $query,
            isset($filters['order']) && isset($filters['order'][0]) ?
                $filters['order'][0]['column'] :
                0,
            isset($filters['order']) && isset($filters['order'][0]) ?
                $filters['order'][0]['dir'] :
                'ASC'
        );

        $ret = array(
            'recordsTotal' => $this->count($query),
            'recordsFiltered' => \count($data),
            'data' => $data,
        );

        return $ret;
    }

    /**
     * GETTERS / SETTERS.
     */

    /**
     * @return string
     */
    public function getNameIdentifier(): string
    {
        return $this->nameIdentifier;
    }

    /**
     * @param string $nameIdentifier
     *
     * @return Datatable
     */
    public function setNameIdentifier(string $nameIdentifier): self
    {
        $this->nameIdentifier = $nameIdentifier;

        return $this;
    }

    /**
     * @param bool $globalSearch
     *
     * @return Datatable
     */
    public function setGlobalSearch(bool $globalSearch): self
    {
        $this->globalSearch = $globalSearch;

        return $this;
    }
}
