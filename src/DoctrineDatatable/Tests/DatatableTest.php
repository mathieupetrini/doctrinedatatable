<?php

namespace DoctrineDatatable\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Tests\OrmTestCase;
use DoctrineDatatable\Datatable;
use Entities\User;

class DatatableTest extends OrmTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entity_manager;

    /**
     * @var Datatable
     */
    private $datatable;

    public function setUp(): void
    {
        parent::setUp();

        $this->entity_manager = $this->_getTestEntityManager();

        $this->datatable = new Datatable(
            $this->entity_manager->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u'),
            'id',
            array()
        );
    }

    public function testGetResult(): void
    {

    }
}