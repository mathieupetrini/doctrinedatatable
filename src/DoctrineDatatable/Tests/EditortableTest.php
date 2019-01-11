<?php

namespace DoctrineDatatable\Tests;

use Doctrine\Tests\Models\CMS\CmsEmail;
use Doctrine\Tests\Models\CMS\CmsUser;
use Doctrine\Tests\Models\DirectoryTree\Directory;
use DoctrineDatatable\Column;
use DoctrineDatatable\Editortable;
use DoctrineDatatable\Exception\MissingData;

/**
 * Class EditortableTest.
 *
 * @codeCoverageIgnore
 */
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

        $this->assertEquals(
            'mpetrini@gmail.com',
            $this->_em->getRepository(CmsUser::class)->findOneBy(array('username' => 'username1'))->getEmail()->email
        );
    }

    /**
     * @throws MissingData
     * @throws \DoctrineDatatable\Exception\MinimumColumn
     */
    public function testEditorWithPrivateAttribute(): void
    {
        $datatable = new Editortable(
            $this->_em->createQueryBuilder()
                ->select('d')
                ->from(Directory::class, 'd'),
            'id',
            array(
                new Column(
                    'name',
                    'd.name',
                    'd.name LIKE :name',
                    '%:name%'
                ),
                new Column(
                    'path',
                    'd.path',
                    'd.path LIKE :path',
                    '%:path%'
                ),
            )
        );

        $datatable->edit(array(
            'data' => array(
                $this->_em->getRepository(Directory::class)->findOneBy(array('name' => 'unittest'))->getId() => array(
                    'path' => 'newpathunittest',
                ),
            ),
        ));

        $this->assertEquals(
            'newpathunittest',
            $this->_em->getRepository(Directory::class)->findOneBy(array('name' => 'unittest'))->getPath()
        );
    }

    /**
     * @throws MissingData
     */
    public function testEditWithoutData(): void
    {
        $this->expectException(MissingData::class);
        $this->datatable->edit(array());
    }

    /**
     * @throws MissingData
     * @throws \DoctrineDatatable\Exception\MinimumColumn
     */
    public function testEditorWithObjectNotFound(): void
    {
        $datatable = new Editortable(
            $this->_em->createQueryBuilder()
                ->select('d')
                ->from(Directory::class, 'd'),
            'id',
            array(
                new Column(
                    'name',
                    'd.name',
                    'd.name LIKE :name',
                    '%:name%'
                ),
                new Column(
                    'path',
                    'd.path',
                    'd.path LIKE :path',
                    '%:path%'
                ),
            )
        );

        $this->assertEmpty(
            $datatable->edit(array(
                'data' => array(
                    -1 => array(
                        'path' => 'newpathunittest',
                    ),
                ),
            ))
        );
    }

    public function testToUpperCamelCase(): void
    {
        $this->assertEquals(
            'Name',
            Editortable::toUpperCamelCase('name')
        );

        $this->assertEquals(
            'FieldGoal',
            Editortable::toUpperCamelCase('field_goal')
        );
    }
}
