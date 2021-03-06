<?php

namespace DoctrineDatatable;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use DoctrineDatatable\Exception\ResolveColumnNotHandle;
use DoctrineDatatable\Exception\UnfilterableColumn;
use DoctrineDatatable\Exception\WhereColumnNotHandle;

/**
 * Class Column.
 *
 * @author Mathieu Petrini <mathieupetrini@gmail.com>
 */
class Column
{
    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|Expr|null
     */
    private $where;

    /**
     * @var string|callable|null
     */
    private $resolve;

    /**
     * @var string|callable|null
     */
    private $aliasOrderby;

    public const GLOBAL_ALIAS = 'value';

    /**
     * Column constructor.
     *
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param string     $alias
     * @param string     $name
     * @param mixed|null $where   (string, \Doctrine\ORM\Query\Expr)
     * @param mixed|null $resolve (string, callable)
     */
    public function __construct(string $alias, string $name, $where = '', $resolve = '', string $aliasOrderby = null)
    {
        $this->alias = $alias;
        $this->name = $name;
        $this->where = $where;
        $this->resolve = $resolve;
        $this->aliasOrderby = $aliasOrderby ?? $alias;
    }

    /**
     * PRIVATE METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param string       $data
     *
     * @throws ResolveColumnNotHandle
     */
    private function setParameter(QueryBuilder &$query, string $data): void
    {
        if (!\is_string($this->resolve) && !\is_callable($this->resolve)) {
            throw new ResolveColumnNotHandle();
        }

        $query->setParameter(
            $this->alias,
            \is_string($this->resolve) ?
                str_replace(':'.$this->alias, $data, $this->resolve) :
                \call_user_func($this->resolve, $data)
        );
    }

    /**
     * PUBLIC METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param mixed        $data
     *
     * @return string
     *
     * @throws ResolveColumnNotHandle
     * @throws WhereColumnNotHandle
     * @throws UnfilterableColumn
     */
    public function where(QueryBuilder &$query, $data): string
    {
        if (null === $this->where) {
            throw new UnfilterableColumn();
        } elseif (!\is_string($this->where) && !$this->where instanceof Expr) {
            throw new WhereColumnNotHandle();
        }

        $this->setParameter($query, $data);

        return (string) $this->where;
    }

    /**
     * GETTERS / SETTERS.
     */

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isHaving(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getAliasOrderBy(): string
    {
        return $this->aliasOrderby;
    }
}
