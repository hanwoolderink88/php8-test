<?php

namespace TestingTimes\App\Repository;

use TestingTimes\App\Entities\User;
use TestingTimes\Persistence\Repository;

class UserRepository extends Repository
{
    public static string $model = User::class;
}
