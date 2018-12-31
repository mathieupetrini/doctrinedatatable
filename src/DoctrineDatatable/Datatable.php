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
    private $resultPerPage;

    /**
     * @var string
     */
    private $nameIdentifier;

    /**
     * @var string
     */
    private const DEFAULT_NAME_IDENTIFIER = 'DT_RowID';

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

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
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
     *
     * @return Datatable
     */
    private function limit(QueryBuilder &$query, int $start): self
    {
        $query->setFirstResult($start)
            ->setMaxResults($this->resultPerPage);

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

        return (int) ($query->select('COUNT(DISTINCT '.$this->processColumnIdentifier($query, false).')')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult());
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @return array
     */
    private function columns(): array
    {
        return array_map(static function (Column $column) {
            return array(
                'data' => $column->getAlias(),
            );
        }, $this->columns);
    }

    /**
     * PUBLIC METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param array  $filtres
     * @param int    $index       (optional) (default=0)
     * @param string $direction   (optional) (default='ASC')
     * @param int    $start       (optional) (default=0)
     * @param bool   $withColumns (optional) (default=false)
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
        int $start = 0,
        bool $withColumns = false
    ): array {
        $query = $this->createQueryResult();
        $this->createFoundationQuery($query, $filtres);

        $data = $this->result($query, $index, $direction, $start);

        $ret = array(
            'recordsTotal' => $this->count($query),
            'recordsFiltered' => \count($data),
            'data' => $data,
        );

        if ($withColumns) {
            $ret['columns'] = $this->columns();
        }

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
}
