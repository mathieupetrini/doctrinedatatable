<?php

declare(strict_types=1);

namespace DoctrineDatatable;

use Doctrine\ORM\Query\ParserResult;

/**
 * Class FacturationDatatable.
 */
abstract class FacturationDatatable extends Datatable
{
    /**
     * ABSTRACT METHODS.
     */

    /**
     * @return string[]
     */
    abstract protected function getSumableColumns(): array;

    /**
     * PRIVATE METHODS.
     */

    /**
     * @return array
     */
    private function calculTotaux(): array
    {
        $query = clone $this->final_query;

        $query->resetDQLPart('orderBy')
            ->resetDQLPart('groupBy');

        $index = 0;
        foreach ($this->getSumableColumns() as $alias => $column) {
            0 === $index ?
                $query->select("$column as $alias") :
                $query->addSelect("$column as $alias");

            ++$index;
        }

        $retour = array();

        foreach ($query->getQuery()->getResult() as $result) {
            foreach ($result as $index => $r) {
                if (!isset($retour['total_'.$index])) {
                    $retour['total_'.$index] = 0;
                }
                $retour['total_'.$index] += (float) $r;
            }
        }

        return $retour;
    }

    /**
     * IMPLEMENT METHODS.
     */

    /**
     * {@inheritdoc}
     */
    public function get(array $filters): array
    {
        $this->createFinalQuery($filters);
        $data = $this->data($filters);

        return array(
            'recordsTotal' => $this->count(),
            'recordsFiltered' => \count($data),
            'data' => $data,
            'totalColonnes' => $this->calculTotaux(),
        );
    }

    /**
     * {@inheritdoc}
     */
    private function count(): int
    {
        $temp = (clone $this->final_query)
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->getQuery();

        /** @var ParserResult $parser */
        $parser = \Tools::callMethod($temp, '_parse');

        list($sqlParams, $types) = \Tools::callMethod(
            $temp,
            'processParameterMappings',
            array(
                $parser->getParameterMappings(),
            )
        );

        return (int) ((clone $this->final_query)->getEntityManager()->getConnection()
            ->executeQuery('SELECT COUNT(*) as total FROM ('.$temp->getSQL().') as t', $sqlParams, $types)
            ->fetch()['total']);
    }
}
