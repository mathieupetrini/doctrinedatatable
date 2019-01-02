<p align="center">
<a href="https://packagist.org/packages/mpetrini/doctrinedatatable"><img src="https://poser.pugx.org/mpetrini/doctrinedatatable/v/stable.svg" alt="Latest Stable Version"></a>
<a class="append-right-8" href="https://codeclimate.com/github/mathieupetrini/doctrinedatatable/maintainability" rel="noopener noreferrer" target="_blank"><img alt="Project badge" aria-hidden="true" class="project-badge" src="https://api.codeclimate.com/v1/badges/2610efbb2a769e72c4e8/maintainability"></a>
<a class="append-right-8" href="https://scrutinizer-ci.com/g/mathieupetrini/doctrinedatatable" rel="noopener noreferrer" target="_blank"><img alt="Project badge" aria-hidden="true" class="project-badge" src="https://scrutinizer-ci.com/g/mathieupetrini/doctrinedatatable/badges/quality-score.png?b=master"></a>
<a class="append-right-8" href="https://gitlab.com/mpetrini/doctrinedatatable" rel="noopener noreferrer" target="_blank"><img alt="Project badge" aria-hidden="true" class="project-badge" src="https://gitlab.com/mpetrini/doctrinedatatable/badges/master/coverage.svg"></a>
<a class="append-right-8" href="https://gitlab.com/mpetrini/doctrinedatatable" rel="noopener noreferrer" target="_blank"><img alt="Project badge" aria-hidden="true" class="project-badge" src="https://gitlab.com/mpetrini/doctrinedatatable/badges/master/pipeline.svg"></a>
<a href="https://packagist.org/packages/mpetrini/doctrinedatatable"><img src="https://poser.pugx.org/mpetrini/doctrinedatatable/license.svg" alt="License"></a>
</p>

# DoctrineDatatable

Deeply based on Doctrine2 package and jQueryDatatable. DoctrineDatatable attempts to take the pain out of development of 
datatable inside yours Web Application.

## Requirements

DoctrineDatatable requires : 

* <a href="http://php.net/">PHP 7.1+</a>
* <a href="https://github.com/doctrine/doctrine2">Doctrine2 2.4+</a>
* <a href="https://github.com/DataTables/DataTables">jQueryDatatable 1.10+</a>

## Install

```bash
composer require mpetrini/doctrinedatatable
```

## Usage

### Basic Usage

#### Unified Filter


```php
<?php

use Doctrine\Tests\Models\CMS\CmsUser;
use DoctrineDataTable\Column;
use DoctrineDataTable\Datatable;

$em = /** instanceof Doctrine\ORM\EntityManager */;

$datatable = new Datatable(
    $em->createQueryBuilder()
        ->select('u')
        ->from(CmsUser::class, 'u'),
    // Primary key of your datatable (Primary key of your entity most of the time)
    'id',
    array(
        new Column(
            // alias
            'name',
            // attribute name with table alias
            'u.name',
            // Where part of the DQL
            'u.name LIKE :global',
            // EQ / GTE / GT / LT / LIKE ...
            '%:global%'
        ),
        new Column(
            'status',
            'u.status',
            // Where part of the DQL
            'u.status LIKE :global',
            // EQ / GTE / GT / LT / LIKE ...
            '%:global%'
        ),
    )
);

echo json_encode(
    $datatable->setGlobalSearch(true)
        ->get($_GET)
);
```

#### Filter by column

```php
<?php

use Doctrine\Tests\Models\CMS\CmsUser;
use DoctrineDataTable\Column;
use DoctrineDataTable\Datatable;

$em = /** instanceof Doctrine\ORM\EntityManager */;

$datatable = new Datatable(
    $em->createQueryBuilder()
        ->select('u')
        ->from(CmsUser::class, 'u'),
    // Primary key of your datatable (Primary key of your entity most of the time)
    'id',
    array(
        new Column(
            // alias
            'name',
            // attribute name with table alias
            'u.name',
            // Where part of the DQL
            'u.name LIKE :name',
            // EQ / GTE / GT / LT / LIKE ...
            '%:name%'
        ),
        new Column(
            'status',
            'u.status'
            // Where And Resolve parts are optional if the column isn't filtered
        ),
    )
);

echo json_encode($datatable->get(
    $_GET
));
```

## Example

See <a href="https://gitlab.com/mpetrini/doctrinedatatable/blob/master/examples">examples</a> directory.

## License

See <a href="https://gitlab.com/mpetrini/doctrinedatatable/blob/master/LICENSE">LICENSE.md</a> file.
