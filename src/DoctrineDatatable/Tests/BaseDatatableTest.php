<?php

namespace DoctrineDatatable\Tests;

use Doctrine\Tests\Models\CMS\CmsUser;
use DoctrineDatatable\Column;
use DoctrineDatatable\Datatable;

abstract class BaseDatatableTest extends BaseTest
{
    /**
     * @throws \DoctrineDatatable\Exception\MinimumColumn
     */
    protected function initDatatable(): void
    {
        $this->datatable = new Datatable(
            $this->_em->createQueryBuilder()
                ->from(CmsUser::class, 'u'),
            'id',
            array(
                new Column(
                    'name',
                    'u.name',
                    'u.name LIKE :name',
                    '%:name%'
                ),
                new Column(
                    'status',
                    'u.status',
                    'u.status = :status',
                    '%:status%'
                ),
            )
        );
    }
}
