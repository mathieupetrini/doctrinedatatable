<?php

namespace DoctrineDatatable\Tests;

use DoctrineDatatable\HavingColumn;
use PHPUnit\Framework\TestCase;

class HavingColumnTest extends TestCase
{
    public function testIsHaving(): void
    {
        $column = new HavingColumn('test', 't.test');
        $this->assertTrue($column->isHaving());
    }
}
