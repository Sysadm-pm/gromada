<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;


class Register extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //protected $connection = 'pgsql2';
    protected $table = "register";
    protected $primaryKey = 'id';
    protected $protected  = [];

    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';


    public function notification()
    {
        return $this->belongsTo(Notification::class, 'register_record_id');
    }

    public function citizens()
    {
        return $this->hasOne(Citizen::class, 'id','citizen_id')->whereLocked(false)->with('documents');
    }

}
