<?php

namespace DoctrineDatatable\Tests\Model;

use Doctrine\Tests\Models\CMS\CmsEmail;

class CmsUser extends \Doctrine\Tests\Models\CMS\CmsUser
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var CmsEmail
     */
    private $email;
}
