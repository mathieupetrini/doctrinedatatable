<?php

namespace DoctrineDatatable\Tests;

use Doctrine\Tests\Models\CMS\CmsEmail;
use Doctrine\Tests\Models\CMS\CmsUser;
use DoctrineDatatable\Column;
use DoctrineDatatable\Editortable;

class EditortableTest extends DatatableTest
{
    /**
     * @var Editortable
     */
    protected $datatable;

    /**
     * @throws \DoctrineDatatable\Exception\MinimumColumn
     */
    protected function initDatatable(): void
    {
        $this->datatable = new Editortable(
            $this->_em->createQueryBuilder()
                ->select('u')
                ->from(CmsUser::class, 'u')
                ->leftJoin('u.email', 'e'),
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
                    ':status'
                ),
                new Column(
                    'email',
                    'e.email',
                    'e.email LIKE :email',
                    '%:email%'
                ),
            )
        );
    }

    /**
     * @throws \DoctrineDatatable\Exception\MissingData
     */
    public function testEditortableStandard(): void
    {
        $this->datatable->edit(array(
            'data' => array(
                $this->_em->getRepository(CmsUser::class)->findOneBy(array('username' => 'username1'))->id => array(
                    'name' => 'Lucien',
                ),
            ),
        ));

        $this->assertInstanceOf(
            CmsUser::class,
            $this->_em->getRepository(CmsUser::class)->findOneBy(array('username' => 'username1'))
        );
    }

    /**
     * @throws \DoctrineDatatable\Exception\MissingData
     */
    public function testEditortableWithOneToOne(): void
    {
        $this->datatable->edit(array(
            'data' => array(
                $this->_em->getRepository(CmsUser::class)->findOneBy(array('username' => 'username1'))->id => array(
                    'email' => $this->_em->getRepository(CmsEmail::class)->findOneBy(array('email' => 'mpetrini@gmail.com'))->id,
                ),
            ),
        ));

        $this->assertInstanceOf(
            CmsEmail::class,
            $this->_em->getRepository(CmsUser::class)->findOneBy(array('username' => 'username1'))->getEmail()
        );
    }
}
