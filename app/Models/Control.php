<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Control extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //protected $connection = 'pgsql2';
    
    protected $table = "temp.my_rtg";
    protected $primaryKey = 'id';
    protected $protected  = [];

    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $casts = [
        'data' =>  'object',
    ];


}
