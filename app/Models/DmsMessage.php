<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;


class DmsMessage extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //protected $connection = 'pgsql2';
    protected $table = "dms_messages";
    //protected $primaryKey = 'id';
    // protected $fillable  = [
    //     "MsgCard",
    //     "MsgNotes",
    //     "MsgImages",
    //     "MsgADBUnit",
    //     "MsgADBUser",
    //     "MsgDate",
    // ];
    protected $protected  = [];
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password',
    // ];

    protected $casts = [
        'msgcard' =>  'object',
        'payload' =>  'object',
    ];

    // public function setDataAttribute($value)
    // {
    //     $this->attributes['data'] = json_decode($value);
    // }

}
