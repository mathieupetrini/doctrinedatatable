<?php

namespace DoctrineDatatable\Tests;

use Doctrine\Tests\Models\CMS\CmsUser;
use Doctrine\Tests\OrmFunctionalTestCase;
use DoctrineDatatable\Column;
use DoctrineDatatable\Datatable;

class DatatableTest extends OrmFunctionalTestCase
{
    /**
     * @var Datatable
     */
    private $datatable;

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function generateFixtures(): void
    {
        $user1 = new CmsUser();
        $user1->status = 'st1';
        $user1->username = 'username1';
        $user1->name = 'name1';

        $user2 = new CmsUser();
        $user2->status = 'st2';
        $user2->username = 'username2';
        $user2->name = 'name2';

        $user3 = new CmsUser();
        $user3->status = 'st3';
        $user3->username = 'username3';
        $user3->name = 'name3';

        $user4 = new CmsUser();
        $user4->status = 'st4';
        $user4->username = 'username4';
        $user4->name = 'name4';

        $this->_em->persist($user1);
        $this->_em->persist($user2);
        $this->_em->persist($user3);
        $this->_em->persist($user4);
        $this->_em->flush();
        $this->_em->clear();
    }

    /**
     * @throws \DoctrineDatatable\Exception\MinimumColumn
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setUp(): void
    {
        $this->useModelSet('cms');
        parent::setUp();

        $this->generateFixtures();

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
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGetResultWithoutFilter(): void
    {
        $result = $this->datatable->get(
            array(),
            0,
            'ASC',
            0
        );

        $this->assertEquals(4, $result['recordsTotal']);
        $this->assertEquals(4, $result['recordsFiltered']);
        $this->assertCount(4, $result['data']);

        $this->assertEquals(
            'st1',
            $result['data'][0]['status']
        );
    }

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGetResultWithDirection(): void
    {
        $result = $this->datatable->get(
            array(),
            1,
            'DESC',
            0
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
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGetResultWithFilter(): void
    {
        $result = $this->datatable->get(
            array(
                'name' => 'name1',
            ),
            0,
            'ASC',
            0
        );

        $this->assertEquals(1, $result['recordsTotal']);
        $this->assertEquals(1, $result['recordsFiltered']);
        $this->assertCount(1, $result['data']);
    }

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGetResultWithFilterNotFound(): void
    {
        $result = $this->datatable->get(
            array(
                'undefined' => 'name1',
            ),
            0,
            'ASC',
            0
        );

        $this->assertEquals(4, $result['recordsTotal']);
        $this->assertEquals(4, $result['recordsFiltered']);
        $this->assertCount(4, $result['data']);
    }
}