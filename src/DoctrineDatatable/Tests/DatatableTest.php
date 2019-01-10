<?php

namespace DoctrineDatatable\Tests;

use Doctrine\Tests\Models\CMS\CmsUser;
use DoctrineDatatable\Column;
use DoctrineDatatable\Datatable;

/**
 * Class DatatableTest.
 *
 * @codeCoverageIgnore
 */
class DatatableTest extends BaseTest
{
    /**
     * @throws \DoctrineDatatable\Exception\MinimumColumn
     */
    protected function initDatatable(): void
    {
        $this->datatable = new Datatable(
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
     * @throws \DoctrineDatatable\Exception\MinimumColumn
     */
    public function testNewDatatableWithoutColumns(): void
    {
        $this->expectException(\DoctrineDatatable\Exception\MinimumColumn::class);

        new Datatable(
            $this->_em->createQueryBuilder()
                ->select('u')
                ->from(CmsUser::class, 'u'),
            'id',
            array()
        );
    }

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGetResultWithoutFilter(): void
    {
        $result = $this->datatable->get(
            array()
        );

        $this->assertEquals(4, $result['recordsTotal']);
        $this->assertEquals(4, $result['recordsFiltered']);
        $this->assertCount(4, $result['data']);

        $this->assertEquals(
            'st1',
            $result['data'][0]['status']
        );

        $result = $this->datatable->get(
            array(
                'order' => array(
                    0 => array(
                        'column' => 1,
                        'dir' => 'desc',
                    ),
                ),
            )
        );

        $this->assertEquals(4, $result['recordsTotal']);
        $this->assertEquals(4, $result['recordsFiltered']);
        $this->assertCount(4, $result['data']);

        $this->assertEquals(
            'st4',
            $result['data'][0]['status']
        );
    }

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGetResultWithFilter(): void
    {
        $result = $this->datatable->get(
            array(
                'columns' => array(
                    array(
                        'search' => array(
                            'value' => 'name1',
                        ),
                    ),
                ),
            )
        );

        $this->assertEquals(1, $result['recordsTotal']);
        $this->assertEquals(1, $result['recordsFiltered']);
        $this->assertCount(1, $result['data']);
    }

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGetResultWithFilterNotFound(): void
    {
        $result = $this->datatable->get(
            array(
                'undefined' => 'name1',
            )
        );

        $this->assertEquals(4, $result['recordsTotal']);
        $this->assertEquals(4, $result['recordsFiltered']);
        $this->assertCount(4, $result['data']);
    }

    public function testSetNameIdentifier(): void
    {
        $this->datatable->setNameIdentifier('ROW_ID');

        $this->assertEquals('ROW_ID', $this->datatable->getNameIdentifier());
    }

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testSetGlobalSearch(): void
    {
        $result = (clone $this->datatable)->setGlobalSearch(true)
            ->get(
                array(
                    'search' => array(
                        'value' => 'name1',
                    ),
                )
            );

        $this->assertEquals(1, $result['recordsTotal']);
        $this->assertEquals(1, $result['recordsFiltered']);
        $this->assertCount(1, $result['data']);
    }
}
