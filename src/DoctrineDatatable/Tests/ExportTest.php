<?php

namespace DoctrineDatatable\Tests;

use DoctrineDatatable\Export;

class ExportTest extends BaseDatatableTest
{
    public function testExport(): void
    {
        $export = (new Export())->setDatatable($this->datatable);
        $content = $export->export();

        $this->assertIsResource($content);

        $data = stream_get_contents($content);
        $this->assertNotEmpty($data);
        rewind($content);
        $this->assertCount(3 * 3, fgetcsv($content, 0, "\t"));
    }

    public function testSetDelimiter(): void
    {
        $export = (new Export())->setDatatable($this->datatable)->setDelimiter(';');
        $content = $export->export();

        $this->assertIsResource($content);

        $data = stream_get_contents($content);
        $this->assertNotEmpty($data);
        rewind($content);
        $this->assertCount(3 * 3, fgetcsv($content, 0, ';'));
    }
}
