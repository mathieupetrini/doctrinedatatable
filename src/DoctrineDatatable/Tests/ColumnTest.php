<?php

namespace DoctrineDatatable\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Tests\OrmTestCase;
use DoctrineDatatable\Column;
use Entities\User;

/**
 * Class ColumnTest.
 */
class ColumnTest extends OrmTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entity_manager;

    /**
     * @var Column
     */
    private static $column;

    /**
     * @var Column
     */
    private static $columnWithResolveCallable;

    public function setUp(): void
    {
        parent::setUp();

        $this->entity_manager = $this->_getTestEntityManager();
    }

    public function testGetName(): void
    {
        $this->assertEquals('e.firstname', self::$column->getName());
    }

    public function testWhereWithStringResolve(): void
    {
        $query = $this->entity_manager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        self::$column->where(
            $query,
            10
        );

        $wherePart = $query->getDQLPart('where');

        $this->assertCount(1, $wherePart->getParts());
        $this->assertEquals(
            'e.firstname = :firstname',
            $wherePart->getParts()[0]
        );

        $this->assertInstanceOf(Parameter::class, $query->getParameter('firstname'));

        $this->assertEquals(
            10,
            $query->getParameter('firstname')->getValue()
        );
    }

    public function testWhereWithCallableResolve(): void
    {
        $query = $this->entity_manager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        self::$columnWithResolveCallable->where(
            $query,
            'N°10'
        );

        $wherePart = $query->getDQLPart('where');

        $this->assertCount(1, $wherePart->getParts());
        $this->assertEquals(
            'e.firstname = :firstname',
            $wherePart->getParts()[0]
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

        self::$columnWithResolveCallable = new Column(
            'firstname',
            'e.firstname',
            'e.firstname = :firstname',
            function (string $parameter): string {
                return preg_replace('/[^0-9]/', '', $parameter);
            }
        );
    }
}
