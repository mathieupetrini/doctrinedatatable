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
     * @var string
     */
    private $identifier;

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

    /**
     * @var QueryBuilder
     */
    protected $query;

    /**
     * @var Column[]
     */
    protected $columns;

    private const DEFAULT_NAME_IDENTIFIER = 'DT_RowId';

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

        foreach ($filters['columns'] as $index => $filter) {
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
     * @return string
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\UnfilterableColumn
     * @throws Exception\WhereColumnNotHandle
     */
    private function createHavingPart(QueryBuilder &$query, array $filters): string
    {
        $temp = '';

        foreach ($filters['columns'] as $index => $filter) {
            if (isset($this->columns[$index]) && !empty($filter['search']['value']) && $this->columns[$index]->isHaving()) {
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
     * @param array        $filters
     *
     * @return Datatable
     */
    private function limit(QueryBuilder &$query, array $filters): self
    {
        $query->setFirstResult($filters['start'] ?? 0)
            ->setMaxResults($filters['length'] ?? $this->resultPerPage);

        return $this;
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     *
     * @return int
     */
    private function count(QueryBuilder $query): int
    {
        $query = clone $query;
        $result = $query->select('COUNT(DISTINCT '.$this->processColumnIdentifier($query, false).') as count')
            ->resetDQLPart('orderBy')
            ->resetDQLPart('groupBy')
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->getQuery()
            ->getScalarResult();

        return !empty($result) ?
            (int) $result[0]['count'] :
            0;
    }

    /**
     * PROTECTED METHODS.
     */

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
    protected function createFoundationQuery(QueryBuilder &$query, array $filters): QueryBuilder
    {
        // If global search we erase all specific where and only keep the unified filter
        if ($this->globalSearch && isset($filters['search']) && !empty($filters['search'][Column::GLOBAL_ALIAS])) {
            $filters = $this->createGlobalFilters($filters);
        }

        $temp = isset($filters['columns']) ?
            $this->createWherePart($query, $filters) :
            '';

        $having = isset($filters['columns']) ?
            $this->createHavingPart($query, $filters) :
            '';

        if (!empty($temp)) {
            $query->andWhere($temp);
        }

        return !empty($having) ?
            $query->andHaving($having) :
            $query;
    }

    /**
     * @return QueryBuilder
     */
    protected function createQueryResult(): QueryBuilder
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
     * @param int          $index
     * @param string       $direction
     *
     * @return array
     */
    protected function result(
        QueryBuilder &$query,
        int $index,
        string $direction
    ): array {
        $this->orderBy($query, $index, $direction);

        return $query->getQuery()
            ->getResult();
    }

    /**
     * PUBLIC METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param array $filters
     *
     * @return array
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\UnfilterableColumn
     * @throws Exception\WhereColumnNotHandle
     */
    public function get(array $filters): array
    {
        $query = $this->createQueryResult();
        $this->createFoundationQuery($query, $filters);

        $data = $this->limit($query, $filters)->result(
            $query,
            isset($filters['order']) ?
                $filters['order'][0]['column'] :
                0,
            isset($filters['order']) ?
                $filters['order'][0]['dir'] :
                'ASC'
        );

        return array(
            'recordsTotal' => $this->count($query),
            'recordsFiltered' => \count($data),
            'data' => $data,
        );
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
