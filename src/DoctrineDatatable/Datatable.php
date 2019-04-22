<?php

namespace DoctrineDatatable;

use Doctrine\ORM\QueryBuilder;
use DoctrineDatatable\Exception\GlobalFilterWithHavingColumn;
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
     * @var QueryBuilder
     */
    protected $final_query;

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
     * @param Column[]     $columns
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
     * @param mixed[] $filters
     *
     * @return mixed[]
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
     * @param mixed[] $filters
     *
     * @return string[]
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\UnfilterableColumn
     * @throws Exception\WhereColumnNotHandle
     */
    private function createCondition(array $filters): array
    {
        $where = '';
        $having = '';

        foreach ($filters['columns'] as $index => $filter) {
            if (isset($this->columns[$index]) && !empty($filter['search']['value'])) {
                $temp = '('.$this->columns[$index]->where($this->final_query, $filter['search']['value']).')';

                $this->columns[$index]->isHaving() ?
                    $having .= (!empty($having) ? ' AND ' : '').$temp :
                    $where .= (!empty($where) ? ' '.($this->globalSearch ? 'OR' : 'AND').' ' : '').$temp;
            }
        }

        return array(
            'where' => $where,
            'having' => $having,
        );
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param bool $withAlias (optional) (default=true)
     *
     * @return string
     */
    private function processColumnIdentifier(bool $withAlias = true): string
    {
        return $this->final_query->getRootAliases()[0].'.'.$this->identifier.($withAlias ? ' AS '.$this->nameIdentifier : '');
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param Column $column
     */
    private function processColumnSelect(Column $column): void
    {
        $this->final_query->addSelect($column->getName().' AS '.$column->getAlias());
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param int    $index
     * @param string $direction
     *
     * @return Datatable
     */
    private function orderBy(int $index, string $direction): self
    {
        $this->final_query->orderBy(
            \array_slice($this->columns, $index, 1)[0]->getAlias(),
            $direction
        );

        return $this;
    }

    /**
     * @param mixed[] $filters
     *
     * @return Datatable
     */
    private function limit(array $filters): self
    {
        $this->final_query->setFirstResult($filters['start'] ?? 0)
            ->setMaxResults($filters['length'] ?? $this->resultPerPage);

        return $this;
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @return int
     */
    private function count(): int
    {
        $query = clone $this->final_query;
        $result = $query->select('COUNT(DISTINCT '.$this->processColumnIdentifier(false).') as count')
            ->resetDQLPart('orderBy')
            ->setFirstResult(0)
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
     * @param mixed[] $filters
     *
     * @return QueryBuilder
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\UnfilterableColumn
     * @throws Exception\WhereColumnNotHandle
     */
    protected function createFoundationQuery(array $filters): QueryBuilder
    {
        // If global search we erase all specific where and only keep the unified filter
        if ($this->globalSearch && isset($filters['search']) && !empty($filters['search'][Column::GLOBAL_ALIAS])) {
            $filters = $this->createGlobalFilters($filters);
        }

        $conditions = isset($filters['columns']) ?
            $this->createCondition($filters) :
            array();

        if (isset($conditions['where']) && !empty($conditions['where'])) {
            $this->final_query->andWhere($conditions['where']);
        }

        if (isset($conditions['having']) && !empty($conditions['having'])) {
            $this->final_query->andHaving($conditions['having']);
        }

        return $this->final_query;
    }

    /**
     * @return QueryBuilder
     */
    protected function createQueryResult(): QueryBuilder
    {
        $this->final_query = clone $this->query;
        $this->final_query->select($this->processColumnIdentifier());
        foreach ($this->columns as $column) {
            $this->processColumnSelect($column);
        }

        return $this->final_query;
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     *
     * @return mixed[]
     */
    protected function result(QueryBuilder $query): array
    {
        return $query->getQuery()->getResult();
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param mixed[] $filters
     *
     * @return mixed[]
     */
    protected function data(array $filters): array
    {
        $this->limit($filters);

        return $this->final_query
            ->getQuery()
            ->getResult();
    }

    /**
     * PUBLIC METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param mixed[] $filters
     *
     * @return QueryBuilder
     *
     * @throws Exception\ResolveColumnNotHandle
     * @throws Exception\UnfilterableColumn
     * @throws Exception\WhereColumnNotHandle
     */
    public function createFinalQuery(array $filters): QueryBuilder
    {
        $this->createQueryResult();
        $this->orderBy(
            isset($filters['order']) ?
                $filters['order'][0]['column'] :
                0,
            isset($filters['order']) ?
                $filters['order'][0]['dir'] :
                'ASC'
        );

        return $this->createFoundationQuery($filters);
    }

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
        $this->createFinalQuery($filters);
        $data = $this->data($filters);

        return array(
            'recordsTotal' => $this->count(),
            'recordsFiltered' => \count($data),
            'data' => $data,
        );
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @return string
     */
    public function export(): string
    {
        return stream_get_contents(
            (new Export())
            ->setDatatable($this)
            ->export()
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
     *
     * @throws GlobalFilterWithHavingColumn
     */
    public function setGlobalSearch(bool $globalSearch): self
    {
        $this->globalSearch = $globalSearch;
        if ($this->globalSearch) {
            foreach ($this->columns as $column) {
                if ($column->isHaving()) {
                    throw new GlobalFilterWithHavingColumn();
                }
            }
        }

        return $this;
    }
}
