<?php

namespace DoctrineDatatable\Tests;

use Doctrine\Tests\Models\CMS\CmsUser;
use DoctrineDatatable\Column;

/**
 * Class ClientSideDatatable.
 */
class ClientSideDatatableTest extends BaseTest
{
    /**
     * @throws \DoctrineDatatable\Exception\MinimumColumn
     */
    protected function initDatatable(): void
    {
        $this->datatable = new \DoctrineDatatable\ClientSideDatatable(
            $this->_em->createQueryBuilder()
                ->select('u')
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

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     */
    public function testGet(): void
    {
        $this->assertCount(1, $this->datatable->get(array()));
    }

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     */
    public function testGetResultWithoutFilter(): void
    {
        $result = $this->datatable->get(
            array()
        );

        $this->assertArrayNotHasKey('recordsTotal', $result);
        $this->assertArrayNotHasKey('recordsFiltered', $result);
        $this->assertCount(4, $result['data']);

        $this->assertEquals(
            'st1',
            $result['data'][0]['status']
        );

        $result = $this->datatable->get(array());

        $this->assertArrayNotHasKey('recordsTotal', $result);
        $this->assertArrayNotHasKey('recordsFiltered', $result);
        $this->assertCount(4, $result['data']);
    }
}
