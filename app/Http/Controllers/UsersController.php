<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Dms;
use Cache;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Redis;

//use Illuminate\Support\Facades\Response;

class UsersController extends Controller
{
  public function __construct()
   {
     //  $this->middleware('auth:api');
   }
   /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
   public function authenticate(Request $request)
   {
       $this->validate($request, [
       'email' => 'required',
       'password' => 'required'
        ]);

        //$user = Users::where('email', $request->input('email'))->first();

        //app('db')->connection()->select('xx');
        //$users = app('db')->connection('pgsql2')->select('public.users');
        // $users = \DB::table('public.dms_receiver')
        //     ->select('dms_receiver.data')
        //     ->get();
        // $users = \DB::connection('pgsql2')->table('public.users')
        //     ->where('username', 'nymalikova')
        //     ->first();
        //$users = User::get();
        $users = User::select('id','username')->where('id', "699351af-b5b4-4e34-a0b5-a9d60dd99b87")->first();
        var_dump($users->toJson());
        exit();
        $dms = Dms::select()->get();
    //$redis = Cache::get('rtg:');
    //Redis::set('name', ['qwe','qwe','qwe']);

    // $values = Redis::lrange('names', 5, 10);
    //Cache::set('name', 'rtg');
    //Cache::set('database.redis.default.database', 2);
    //Cache::put('laravel_key_db2', '2', 1);  // Gets set in db 2
    
    
    if($redis = Redis::command('KEYS', ['*699351af-b5b4-4e34-a0b5-a9d60dd99b87:72a26935-88e8-417e-9333-ff3cc1379821*'])){
      $values = Redis::get($redis[0]);
    }else{
      return response()->json(['status' => 'fail'], 200);
    }

    $camel = json_decode($values, true);
      //$dms2 = $dms->toArray();
    var_dump($camel["userId"]);
      exit();
        return response()->json(['data' => $dms2]);
        //return response()->json(['status' => 'fail'], 200);
        //return json_encode($users->id, true);

     if(Hash::check($request->input('password'), $user->password)){
          //$apikey = base64_encode(str_random(40));
          Users::where('email', $request->input('email'))->update(['api_key' => "$apikey"]);
          return response()->json(['status' => 'success','api_key' => $apikey]);
      }else{
          return response()->json(['status' => 'fail'],401);
      }
   }
}
