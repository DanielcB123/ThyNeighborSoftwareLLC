<?php

namespace App\Shared\Database;

use Illuminate\Database\Eloquent\Model;

abstract class CentralModel extends Model
{
    protected $connection = 'central';
}
