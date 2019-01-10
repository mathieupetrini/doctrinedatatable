<?php

namespace DoctrineDatatable;

use Doctrine\ORM\Query\Expr\Join;
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

        $tab = array();
        foreach ($this->processEditing($params) as $index => $entity) {
            $tab[$index] = array();
            foreach ($this->columns as $column) {
                $table_alias = array();
                preg_match('/^[^\.]{1,}/', $column->getName(), $table_alias);

                /**
                 * @var array $temp
                 */
                $temp = array_filter(
                    $this->query->getDQLPart('join'),
                    function (array $join) use($table_alias) {
                        return $join[0]->getAlias() === $table_alias[0];
                    }
                );

                if ($table_alias[0] === $this->query->getAllAliases()[0]) {
                    $tab[$index][$column->getAlias()] = $entity->{'get'.self::toUpperCamelCase($column->getAlias())}();
                } else {
                    $attribute_name = str_replace(
                        array_keys($temp)[0].'.',
                        '',
                        reset($temp)[0]->getJoin()
                    );

                    $child_object = $entity->{'get'.self::toUpperCamelCase($attribute_name)}();

                    $tab[$index][$column->getAlias()] = $child_object->{'get'.self::toUpperCamelCase($column->getAlias())}();
                }


            }
        }

        return $tab;
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
