<?php

namespace DoctrineDatatable;

use Doctrine\ORM\QueryBuilder;
use DoctrineDatatable\Exception\MissingData;

/**
 * Class Editortable.
 */
class Editortable extends Datatable
{
    /**
     * @var string
     */
    private $rootClass;

    /**
     * @var array
     */
    private $parameters;

    /**
     * Editortable constructor.
     *
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param QueryBuilder $query
     * @param string       $identifier
     * @param array        $columns
     * @param array        $parameters
     *
     * @throws Exception\MinimumColumn
     */
    public function __construct(QueryBuilder $query, string $identifier, array $columns, array $parameters = array())
    {
        parent::__construct($query, $identifier, $columns);
        $this->rootClass = $query->getRootEntities()[0];
        $this->parameters = $parameters;
    }

    /**
     * PRIVATE METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param object $entity
     * @param string $field
     * @param mixed  $value
     *
     * @return array
     */
    private function processValue(object $entity, string $field, $value): array
    {
        $ccField = self::toUpperCamelCase($field);

        $field = property_exists($entity, $ccField) ? $ccField : $field;
        if (property_exists($entity, $field)) {
            $associations = $this->query->getEntityManager()->getClassMetadata($this->rootClass)->associationMappings;
            if (isset($associations[$field])) {
                $value = $this->query->getEntityManager()->getRepository(
                    $associations[$field]['targetEntity']
                )->find($value);
            }
        }

        return array(
            'field' => $field,
            'setter' => 'set'.$ccField,
            'value' => $value,
        );
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param object $entity
     * @param array  $data
     *
     * @return object[]
     */
    private function processRowEditing(object $entity, array $data): array
    {
        $entities = array();
        foreach ($data as $field => $value) {
            $property = $this->processValue($entity, $field, $value);

            if (isset($entity->{$property['field']})) {
                $result = $entity->{$property['field']} = $property['value'];
            } elseif (method_exists($entity, $property['setter'])) {
                $result = $entity->{$property['setter']}($property['value']);
            }

            if (isset($result) && \is_object($result)) {
                $entities[] = $result;
            }
        }

        return $entities;
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param array $params
     *
     * @return array
     */
    private function processEditing(array $params): array
    {
        $entities = array();
        $repository = $this->query->getEntityManager()->getRepository($this->rootClass);
        foreach ($params['data'] as $id => $row) {
            $entity = $repository->find($id);
            if (\is_object($entity)) {
                $entities = array_merge(
                    $entities,
                    $this->processRowEditing($entity, $row)
                );
            }
        }

        return $entities;
    }

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param object[] $entities
     *
     * @return array
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function getResultsAfterEdit(array $entities): array
    {
        if (empty($entities)) {
            return array();
        }

        $this->query->getEntityManager()->flush($entities);

        return $this->createQueryResult()
            ->where($this->query->getRootAliases()[0].' IN (:entities)')
            ->setParameters(array_merge(
                $this->parameters,
                array(
                    'entities' => $entities,
                )
            ))
            ->getQuery()
            ->getResult();
    }

    /**
     * PUBLIC METHODS.
     */

    /**
     * @author Mathieu Petrini <mathieupetrini@gmail.com>
     *
     * @param array $params
     *
     * @return array
     *
     * @throws MissingData
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edit(array $params): array
    {
        if (!isset($params['data'])) {
            throw new MissingData();
        }

        return $this->getResultsAfterEdit(
            $this->processEditing($params)
        );
    }

    /**
     * @param string $field
     *
     * @return string
     */
    public static function toUpperCamelCase(string $field): string
    {
        return preg_replace_callback(
            '/_(.?)/',
            function (array $matches): string {
                return strtoupper($matches[1]);
            },
            ucfirst($field)
        );
    }
}
