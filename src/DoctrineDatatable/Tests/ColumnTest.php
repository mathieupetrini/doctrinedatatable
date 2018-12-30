<?php

namespace DoctrineDatatable\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Tests\OrmTestCase;
use DoctrineDatatable\Column;
use DoctrineDatatable\Tests\Models\User;

/**
 * Class ColumnTest.
 */
class ColumnTest extends OrmTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Column
     */
    private static $column;

    /**
     * @var Column
     */
    private static $column_with_resolve_callable;

    public function setUp(): void
    {
        parent::setUp();

        $this->em = $this->_getTestEntityManager();
    }

    public function testGetName(): void
    {
        $this->assertEquals('e.firstname', self::$column->getName());
    }

    public function testWhereWithStringResolve(): void
    {
        $query = $this->em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        self::$column->where(
            $query,
            10
        );

        $where_part = $query->getDQLPart('where');

        $this->assertCount(1, $where_part->getParts());
        $this->assertEquals(
            'e.firstname = :firstname',
            $where_part->getParts()[0]
        );

        $this->assertInstanceOf(Parameter::class, $query->getParameter('firstname'));

        $this->assertEquals(
            10,
            $query->getParameter('firstname')->getValue()
        );
    }

    public function testWhereWithCallableResolve(): void
    {
        $query = $this->em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        self::$column_with_resolve_callable->where(
            $query,
            'N°10'
        );

        $where_part = $query->getDQLPart('where');

        $this->assertCount(1, $where_part->getParts());
        $this->assertEquals(
            'e.firstname = :firstname',
            $where_part->getParts()[0]
        );

        $this->assertInstanceOf(Parameter::class, $query->getParameter('firstname'));

        $this->assertEquals(
            10,
            $query->getParameter('firstname')->getValue()
        );
    }

    public static function setUpBeforeClass(): void
    {
        self::$column = new Column(
            'firstname',
            'e.firstname',
            'e.firstname = :firstname',
            ':firstname'
        );

        self::$column_with_resolve_callable = new Column(
            'firstname',
            'e.firstname',
            'e.firstname = :firstname',
            function (string $parameter): string {
                return preg_replace('/[^0-9]/', '', $parameter);
            }
        );
    }
}
