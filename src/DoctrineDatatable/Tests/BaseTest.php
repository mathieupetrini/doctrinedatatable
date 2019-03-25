<?php

namespace DoctrineDatatable\Tests;

use Doctrine\Tests\Models\CMS\CmsEmail;
use Doctrine\Tests\Models\CMS\CmsPhonenumber;
use Doctrine\Tests\Models\CMS\CmsUser;
use Doctrine\Tests\Models\DirectoryTree\Directory;
use Doctrine\Tests\OrmFunctionalTestCase;
use DoctrineDatatable\Datatable;

abstract class BaseTest extends OrmFunctionalTestCase
{
    /**
     * @var Datatable
     */
    protected $datatable;

    abstract protected function initDatatable(): void;

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function generateFixtures(): void
    {
        $user1 = new CmsUser();
        $user1->status = 'st1';
        $user1->username = 'username1';
        $user1->name = 'name1';
        $phone = new CmsPhonenumber();
        $phone->phonenumber = '0102030405';
        $user1->addPhonenumber($phone);
        $phone = new CmsPhonenumber();
        $phone->phonenumber = '0102030406';
        $user1->addPhonenumber($phone);

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

        $email = new CmsEmail();
        $email->email = 'mathieupetrini@gmail.com';

        $user1->setEmail($email);

        $email2 = new CmsEmail();
        $email2->email = 'mpetrini@gmail.com';

        $directory = new Directory();
        $directory->setName('unittest');
        $directory->setPath('coucou/');

        $this->_em->persist($user1);
        $this->_em->persist($user2);
        $this->_em->persist($user3);
        $this->_em->persist($user4);
        $this->_em->persist($email2);
        $this->_em->persist($directory);
        $this->_em->flush();
        $this->_em->clear();
    }

    /**
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setUp(): void
    {
        $this->useModelSet('cms');
        $this->useModelSet('directorytree');
        parent::setUp();

        $this->generateFixtures();
        $this->initDatatable();
    }
}
