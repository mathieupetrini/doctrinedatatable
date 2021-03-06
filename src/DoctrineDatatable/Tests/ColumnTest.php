<?php

namespace DoctrineDatatable\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Tests\OrmTestCase;
use DoctrineDatatable\Column;
use Entities\User;

/**
 * Class ColumnTest.
 *
 * @codeCoverageIgnore
 */
class ColumnTest extends OrmTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

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

        $this->entityManager = $this->_getTestEntityManager();
    }

    public function testGetAlias(): void
    {
        $this->assertEquals('firstname', self::$column->getAlias());
    }

    public function testGetName(): void
    {
        $this->assertEquals('e.firstname', self::$column->getName());
    }

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     */
    public function testWhereWithStringResolve(): void
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        $query->andWhere(self::$column->where(
            $query,
            10
        ));

        $wherePart = $query->getDQLPart('where');

        $this->assertNotNull($wherePart);
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

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     */
    public function testWhereWithCallableResolve(): void
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        $query->andWhere(self::$columnWithResolveCallable->where(
            $query,
            'N°10'
        ));

        $wherePart = $query->getDQLPart('where');

        $this->assertNotNull($wherePart);
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

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     */
    public function testWhereWithNotResolvedColumnType(): void
    {
        $this->expectException(\DoctrineDatatable\Exception\ResolveColumnNotHandle::class);

        $column = new Column(
            'firstname',
            'e.firstname',
            'e.firstname = :firstname',
            10
        );

        $query = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        $column->where($query, 10);
    }

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     */
    public function testWhereWithNotResolvedWhereType(): void
    {
        $this->expectException(\DoctrineDatatable\Exception\WhereColumnNotHandle::class);

        $column = new Column(
            'firstname',
            'e.firstname',
            10,
            ':firstname'
        );

        $query = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        $column->where($query, 10);
    }

    /**
     * @throws \DoctrineDatatable\Exception\ResolveColumnNotHandle
     * @throws \DoctrineDatatable\Exception\UnfilterableColumn
     * @throws \DoctrineDatatable\Exception\WhereColumnNotHandle
     */
    public function testWhereWithUnfilterableColumn(): void
    {
        $this->expectException(\DoctrineDatatable\Exception\UnfilterableColumn::class);

        $column = new Column(
            'firstname',
            'e.firstname',
            null
        );

        $query = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        $column->where($query, 10);
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

    public function testIsHaving(): void
    {
        $column = new Column('test', 't.test');
        $this->assertFalse($column->isHaving());
    }
}
