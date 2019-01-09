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
     * {@inheritdoc}
     */
    public function __construct(QueryBuilder $query, string $identifier, array $columns, ?int $resultPerPage = self::RESULT_PER_PAGE)
    {
        parent::__construct($query, $identifier, $columns, $resultPerPage);
        $this->rootClass = $query->getRootEntities()[0];
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
        $ccField = preg_replace_callback(
            '/_(.?)/',
            function (array $matches): string {
                return strtoupper($matches[1]);
            },
            ucfirst($field)
        );

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
     */
    private function processRowEditing(object $entity, array $data): void
    {
        foreach ($data as $field => $value) {
            $property = $this->processValue($entity, $field, $value);

            if (isset($entity->{$property['field']})) {
                $entity->{$property['field']} = $property['value'];
            } elseif (method_exists($entity, $property['setter'])) {
                $entity->{$property['setter']}($property['value']);
            }
        }
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
                $this->processRowEditing($entity, $row);
                $entities[] = $entity;
            }
        }

        return $entities;
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
     */
    public function edit(array $params): array
    {
        if (!isset($params['data'])) {
            throw new MissingData();
        }

        return $this->processEditing($params);
    }
}