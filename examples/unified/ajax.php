<?php

require __DIR__.'/../../tests/autoload.php';

$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
    array(__DIR__.'./../../vendor/doctrine/orm/tests/Doctrine/Tests/Models/CMS/'),
    false,
    __DIR__.'/../proxies/',
    new \Doctrine\Common\Cache\ArrayCache(),
    true
);

$connectionParams = array(
    'url' => 'sqlite:///'.__DIR__.'/../example.sqlite',
);

$entityManager = \Doctrine\ORM\EntityManager::create(
    \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config),
    $config
);

$datatable = new \DoctrineDatatable\Datatable(
    $entityManager->createQueryBuilder()
        ->select('u')
        ->from(\Doctrine\Tests\Models\CMS\CmsUser::class, 'u'),
    'id',
    array(
        new \DoctrineDatatable\Column(
            'name',
            'u.name',
            'u.name LIKE :name',
            '%:name%'
        ),
        new \DoctrineDatatable\Column(
            'status',
            'u.status',
            'u.status = :status',
            ':status'
        ),
    )
);

echo json_encode(
    $datatable->setGlobalSearch(true)
        ->get($_POST)
);