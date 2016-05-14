<?php

namespace Controller;

use Model\User;

class UserController
{
    public function index()
    {
        $user = new User();
        $list = $user->select();
        //var_dump($list);

        include './View/User/index.php';

    }

    public  function create()
    {

    }
}