<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;


class Dms extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //protected $connection = 'pgsql2';
    protected $table = "dms_receiver";
    protected $primaryKey = 's_id';
    protected $fillable  = [
        'out_original_address',
        'out_building_id',
        'out_residence_id',
        'out_street_id',
        'out_district_id',
        'out_locality_id',
        'out_country_id',
        'original_addressdress',
        'residence_id',
        'building_id',
        'street_id',
        'district_id',
        'locality_id',
        'country_id',
        'is_mother_address',
        'status',
        'note',
        'user_id',
        'organization_id',
        'version'
    ];
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
        'data' =>  'object',
    ];
    // public function setDataAttribute($value)
    // {
    //     $this->attributes['data'] = json_decode($value);
    // }

}