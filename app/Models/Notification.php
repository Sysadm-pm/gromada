<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
// use Illuminate\Support\Facades\DB;


class Notification extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    //protected $connection = 'pgsql2';
    protected $table = "trembita_dms_notifications";
    //protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $protected  = [];
    // protected $fillable  = [
    //     'out_original_address',
    //     'out_building_id',
    //     'out_residence_id',
    //     'out_street_id',
    //     'out_district_id',
    //     'out_locality_id',
    //     'out_country_id',
    //     'original_addressdress',
    //     'residence_id',
    //     'building_id',
    //     'street_id',
    //     'district_id',
    //     'locality_id',
    //     'country_id',
    //     'is_mother_address',
    //     'status',
    //     'note',
    //     'user_id',
    //     'organization_id',
    //     'version'
    // ];
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';


    // $query = DB::table('trembita_dms_notifications')
    //         ->join('tbl_citizens', 'register.citizen_id', '=', 'tbl_citizens.id')
    //         ->select('trembita_dms_notifications.*', 'tbl_citizens.id', 'tbl_citizens.sex')
    //         ->get();

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password',
    // ];
    // protected $casts = [
    //     'data' =>  'object',
    // ];
    // public function setDataAttribute($value)
    // {
    //     $this->attributes['data'] = json_decode($value);
    // }

    public function registers()
    {
        return $this->hasOne(Register::class, 'id','register_record_id')->with('citizens');//->whereLocked(false)->where('is_active', true )->with('citizens');
    }


    // public function citizens()
    // {
    //     return $this->registers()->hasOne(Citizen::class, 'id','citizen_id');//->whereLocked(false)->where('is_active', true )->with('citizens');
    // }

    public function test()
    {
    //    return $this->registers()->hasOne(Citizen::class, 'id','citizen_id')->join('tbl_citizens', 'register.citizen_id', '=', 'tbl_citizens.id');//->whereLocked(false)->where('is_active', true )->with('citizens');
    }

    // public function carOwner()
    // {
    //     return $this->hasOneThrough(
    //         Citizen::class,
    //         Register::class,
    //         'citizen_id', // citizen_id
    //         'id', // tbl_citizens
    //         'id', // ?
    //         'citizen_id', // register
    //     );

    // }

    public function scopeWithDoc($query)
    {
        $query
        ->addSelect('n.id as n_id') //93886
        ->addSelect('n.register_record_id')
        ->addSelect('n.notified')
        ->addSelect('n.response_status')
        ->addSelect('n.response_note')
        ->addSelect('n.response_id')
        ->addSelect('n.locked')
        ->addSelect( \DB::raw("(to_char((\"n\".\"created\"), 'DD.MM.YYYY HH24:MI:SS'::text)) as n_created") )
        ->addSelect( \DB::raw("(to_char((\"n\".\"updated\"), 'DD.MM.YYYY HH24:MI:SS'::text)) as n_updated") )
        ->addSelect('n.max_retries_reached')
        ->addSelect('n.cancel_notification_id')
        ->addSelect('n.package_id')
        ->addSelect('n.received_json')
        ->addSelect('r.citizen_id')
        ->addSelect('r.registration_status')
        ->addSelect('r.next_record')
        ->addSelect( \DB::raw("(to_char((\"r\".\"created\"), 'DD.MM.YYYY HH24:MI'::text)) as r_created") )
        ->addSelect( \DB::raw("(to_char((\"r\".\"updated\"), 'DD.MM.YYYY HH24:MI'::text)) as r_updated") )
        ->addSelect('r.init_date')
        ->addSelect('r.district_id')
        ->addSelect('c.sex')
        ->addSelect('c.creation_reason')
        ->addSelect('c.eddr_id')
        ->addSelect('c.date_of_birth')
        ->addSelect('c.ipn')
        ->addSelect('d.first_name')
        ->addSelect('d.last_name')
        ->addSelect('d.middle_name')
        ->addSelect('d.code')
        ->addSelect('d.issue_date')
        ->addSelect('d.issued_by')
        ->addSelect('d.valid_date')
        ->addSelect('d.id as d_id')
        ->from('trembita_dms_notifications AS n')//Черга повідомлень на ДМС
        // ->whereNotNull('dc.Id')
        ->leftjoin('public.register as r','r.id', '=', 'n.register_record_id')//Картка реєстрового запису
        ->leftjoin('public.tbl_citizens as c','c.id', '=', 'r.citizen_id')//Картка реєстрового запису
        ->leftjoin('public.documents as d','d.citizen_id', '=', 'c.id')//Картка реєстрового запису
        ->where('d.locked', false)
        ->where('c.locked', false)
        ->where('r.locked', false)
        ->where('d.is_active', true)
        ->where('r.is_active', true)
            ;
    }

}
