<?php

namespace DoctrineDatatable;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use DoctrineDatatable\Exception\ResolveColumnNotHandle;
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
     * @var string|Expr
     */
    private $where;

    /**
     * @var string|callable
     */
    private $resolve;

    /**
     * Column constructor.
     *
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param string $alias
     * @param string $name
     * @param mixed  $where   (string, \Doctrine\ORM\Query\Expr)
     * @param mixed  $resolve (string, callable)
     */
    public function __construct(string $alias, string $name, $where, $resolve)
    {
        $this->alias = $alias;
        $this->name = $name;
        $this->where = $where;
        $this->resolve = $resolve;
    }

    /**
     * MAGIC METHODS.
     */

    /**
     * @param string $prop
     *
     * @return mixed
     */
    public function __get(string $prop)
    {
        return $this->$prop;
    }

    /**
     * @param string $prop
     *
     * @return bool
     */
    public function __isset(string $prop): bool
    {
        return isset($this->$prop);
    }

    /**
     * PRIVATE METHODS.
     */
    private function setParameter(QueryBuilder &$query, string $data): void
    {
        if (\is_string($this->resolve)) {
            $query->setParameter(
                $this->alias,
                str_replace(':'.$this->alias, $data)
            );
        } elseif (\is_callable($this->resolve)) {
            $query->setParameter(
                $this->alias,
                $this->resolve($data)
            );
        } else {
            throw new ResolveColumnNotHandle();
        }
    }

    /**
     * PUBLIC METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     */
    public function where(QueryBuilder &$query, $data): void
    {
        if (\is_string($this->where) || $this->where instanceof Expr) {
            $query->andWhere($this->where);
        } else {
            throw new WhereColumnNotHandle();
        }

        $this->setParameter($query, $data);
    }

    /**
     * GETTERS / SETTERS.
     */

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
