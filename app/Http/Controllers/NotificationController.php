<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dms;
use App\Models\DmsTest;
use App\Models\Notification;
use App\Models\Result;
use App\Models\Citizen;
use App\Models\Document;
use App\Models\Control;
use Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use TelegramBot\Api\BotApi;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Search citizen by PIB.
     *
     * @return \Illuminate\Http\Response
     */
    public function uuidSearch(Request $request)
    {

        $this->validate($request, [
            'pib' => 'required_without_all:unzr,rnokpp|max:255',
            'rnokpp' => 'required_without_all:pib,unzr|digits:10',
            'unzr' => 'required_without_all:pib,rnokpp|max:14|regex:/[0-9]{8}-[0-9]{5}/',
            // 'first_name' => 'required|max:255',
            // 'middle_name' => 'filled',
            // 'last_name' => 'string|min:2',
            // 'birthday' => 'string|10',
        ],[
            'regex' => 'УНЗР має містити 8 цифр тире і ще 5 цифр. Приклад: 12345678-12345',
            'digits' => 'РНОКПП має містити 10 цифр.',
        ]);
        if($request->unzr || $request->rnokpp){return $request->input();}
        preg_match_all('/\[(.*?)\]/', $request->pib, $m , PREG_OFFSET_CAPTURE);
        $str2 = preg_replace('/(\[.+?)+(\])/i', '', $request->pib);
        $citizenArray = explode(' ',$str2);
        if(($m[1][0][1]??0) === 1){
            $last_name = ($m[1][0][0]??($citizenArray[0]??""))."%";
            $first_name = ($m[1][1][0]??($citizenArray[1]??""))."%";
        }else{
            $last_name = (($citizenArray[0]??""))."%";
            $first_name = ($m[1][0][0]??($citizenArray[1]??""))."%";
        }
        // return [
        //     "str1"=>$str1,
        //     "str2"=>$str2,
        //     "last_name"=> $last_name,
        //     "first_name"=> $first_name,
        //     "middle_name"=> ($citizenArray[2]??"")."%",
        //     "date_of_birth"=> ($citizenArray[3]??"")."%",
        //     // "m"=>$m[1]
        // ];

        if(count($citizenArray)<=4 && count($citizenArray)>0){
            $getCitizen = Document::select(
                [
                    "documents.citizen_id",
                    "documents.last_name",
                    "documents.first_name",
                    "documents.middle_name",
                    "c.date_of_birth",
                    "c.date_of_death",
                ]
            )
            ->leftJoin('tbl_citizens as c', 'c.id', '=', 'documents.citizen_id')
            ->orderBy('documents.created', 'desc')
            // ->where('id', 'ilike', $citizenArray[0])
            ->where('last_name', 'ilike', $last_name)
            ->where('first_name', 'ilike', $first_name)
            ->where('middle_name', 'ilike', ($citizenArray[2]??"")."%")
            ->where('date_of_birth', 'ilike', ($citizenArray[3]??"")."%")
            ->where('is_active', true)
            ->skip(0)
            ->take(10)
            ->get()
            ;
            return $getCitizen;
        }
        return "To many words in string.";
        // $getCitizen = Citizen::find()->where()
        // return $request;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     /**
     * @OA\Get(
     *     path="/api/notifications",
     *     tags={"notifications"},
     *     @OA\Response(
     *         response="200",
     *         description="Returns some sample category things",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        // \DB::enableQueryLog();
        $result = Notification::OrderBy('n.created','desc')
        ->withDoc()
        ->simplePaginate(100)
        // ->get()
        ;
        //dd(\DB::getQueryLog());
        return $result;
die;

        $result = Notification::query();
        //$temp = Notification::all()->get();
        //dd($result);
        // if($request->page1){
            // \DB::enableQueryLog();


            // $query = DB::table('trembita_dms_notifications')
            // //->join('register', 'register.id', '=', 'trembita_dms_notifications.register_record_id')
            // //->join('tbl_citizens', 'tbl_citizens.id', '=', 'register.citizen_id')
            // //->select('trembita_dms_notifications.*')
            // ->get()
            // ->limit(1);
            // // ->orderBy('updated', 'desc')

            // dd(\DB::getQueryLog());
            // return response($query->items());
        // }
        // if (!empty($request->filter["status"])) {
        //     //$result = $result->where('status', $request->filter["status"]);
        // }

        // if (!empty($request->filter['created|>='])) {
        //     if (empty($request->filter['created|<='])) {
        //         $end_date = Carbon::now('Europe/Kiev')->toDateTimeString();
        //     } else {
        //         $end_date = Carbon::parse(trim($request->filter['created|<='], ":999"))->endOfDay()->toDateTimeString();
        //     }
        //     $start_date = Carbon::parse($request->filter["created|>="])->startOfDay()->toDateTimeString();

        //     $result = $result->whereBetween('created', [$start_date, $end_date]);

        // }

        // if (!empty($request->filter["districtId"])) {
        //     $result = $result->where(function ($query) use ($request) {
        //                     $query->where('type', 'ER-11')
        //                         ->where('out_district_id', trim($request->filter["districtId"]))
        //                         ->orWhere('district_id', trim($request->filter["districtId"]));
        //                 });
        // }

//        \DB::enableQueryLog(); // Enable query log
        if ($request->page) {
            //dd($request->page);
           //$result = $result->with('registers')->where('locked', false)->orderBy('updated', 'desc')->simplePaginate(100);
            //$result = $result->simplePaginate(20);//->where('locked', false)->orderBy('updated', 'desc')->simplePaginate(20);
            //dd($result->items());
            $status = 'вибув з проживання';
            $registerIsActive = true;
            $registerLocked = false;
            $result = $result->whereHas('registers' , function($query) use ($status,$registerIsActive,$registerLocked){
                $query
                ->where('locked', $registerLocked)
                ->where('is_active', $registerIsActive )
                // ->whereHas('citizens' , function($query) {
                //     $query
                //         ->whereHas('documents' , function($query) {
                //         $query
                //         ->where('last_name', 'like', '%Петренко%')
                //         ->where('first_name', 'like', '%Кирил%')
                //         ->where('middle_name', 'like', '%Максимович%')
                //         ;})
                //     //->where('ipn', 3515503482 )
                //     //->where('eddr_id', '19911105-08979' )
                //     ;})
                // //->where('registration_status', $status)
                ;})
                ->with('registers')
                //->where('locked', false)
                //->where('notified',  true)
                // ->where(function($q) {
                //     $q->where('response_status',  'REJECTED')
                //       ->orWhere('response_status',  null);
                // })
                ->orderBy('updated', 'desc')
                ->Paginate($request->limit);

            // $result = $result->whereHas('citizens' , function($query){
            //     $query->where('sex', 'ч' );
            //   });
            // with(['citizens' => function($query) {
            //     $query->where('sex', 'ч' );
            // }])

//            dd(\DB::getQueryLog()); // Show results of log


           return response($result);//->items())
        //    ->header('x-total-count', $result->total())
        //    ->header('X-Header-Two', 'Header Value');//->items());;

        } else {
            $result = $result->
            orderBy('updated', 'desc')->
            where('locked', false)->
            skip(0)->
            take(100)->
            get(); //->where('processed', false)
            return response($result);
        }
        //dd($result);



    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'todo' => 'required',
            'description' => 'required',
            'category' => 'required'
        ]);
        if (Auth::user()->todo()->Create($request->all())) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'fail']);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        require_once '/home/.../dms/InfMsgReceiver/processedDms.class.php';
        $nor = new \pracessedDms();
        $nor->updateById($id);
        $todo = Dms::first()->where('s_id', $id)->where('locked', false)->get();
        //dd($todo);
        if (isset($todo[0])) {
            return response($todo[0]);
        } else {
            return response()->json(['Результат' => 'Данні з ID: \'' . $id . '\'  відсутні.'], 404);
        }
        //return response()->json($todo);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $dms = Dms::where('s_id', $id)->get();
        return view('todo.edittodo', ['dms' => $dms]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        require_once '/home/.../dms/InfMsgReceiver/api-rtgk.class.php';
        require_once '/home/.../fleita/HSM.class.php';
        //require_once '/home/.../dms/InfMsgReceiver/sendDms.php'; //Only for prod!!!
        //require_once '/home/.../dms/test-InfMsgReceiver/test-receiver.php';

        $tgToken = "";//
        $chatId = ""; //"title": "вхідні заяви"
        try {
            $bot = new BotApi($tgToken);
        } catch (\Throwable $th) {
            error_log(date('Y-m-d H:i:s')  . " ERROR:\n"
            . "Error create TG bot object "
            . "\n - sourceID=" . $requestID
            . ";\n - Error massage = " . $th->getMessage()
            . ";\n\n", 3, "/home/.../rtgk_back/app/Http/Controllers/logs/tg.log");
        }



        if ($dms->status == "new") {


                $request->merge(['version' => ++$dms->version]);
                if ($dms->fill($request->all())->save()) {
                    return response()->json(['Результат' => 'Данні збережено.'], 200);
                } else {
                    return response()->json(['Помилка відправки' => 'Необхідно скинути кеш, оновити сторінку та спробувати ще раз.'], 500);
                }
        }


        $camel = new \RtgkApi(
            "",
            "",
            ""
        );


        $request->user_id = $request->user_id ?? "";

        if(!empty($request->user_id)){
            $fioEmploeeArray = json_decode($camel->getFIO($request->user_id), true);
            $fioEmploee = $fioEmploeeArray["lastName"]." ".$fioEmploeeArray["firstName"]." ".$fioEmploeeArray["middleName"];
        }else{
            $fioEmploee = "";
            return response()->json(['Помилка відправки' => 'Помилка данних.Спробуйте скинути кеш браузера та оновити сторінку.'], 500);
        }


        $hsm = new \HSM($result->requestData, $bearer, "cihsm://gioc-site-1/shard-1.19b725e", "111113111113");

        if (empty($hsm)) {
                $botMessageErr2 = "🟥[ <code>" . $dms->id . "</code> ] | <b>Помилка відправки даних!</b>❌
        ➡️Запит№: [ " . $dms->id . " ]
        ➡️ДМС№: [ " . $dms->requestid_dms . " ]
        ➡️<b>Дія№</b>: [ <code>" . $dms->id . "</code> ]
        ℹ️status: [ " . $request->status . " ] ".$markerForBot."
        ℹ️note: [ <code>" . $request->note . "</code> ]
        👤ID користувача№: [ <code>" . $request->user_id . "</code> ] ({$fioEmploee})
        📝Тіло запиту: [ <code>" . json_encode($exitForBot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</code> ]
        🔴Код відмови: <code>". $resp["status"]."</code>
        🔴Текст відмови: <code>\"Сервер електронного підпису не відповів на запит системи.Спробуйте ще раз пізніше.\"</code> ";

            $bot->editMessageText($chatId, $messageId, $botMessageErr2, "html");
            return response()->json(['Результат' => 'Сервер електронного підпису не відповів на запит системи.Спробуйте ще раз пізніше.'], 500);
        }


        return response()->json(['Error' => 'Відправка на ДМС не здійснена.', 'Помилка відправки' => $resp], 500);

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // if (Todo::destroy($id)) {
        //     return response()->json(['status' => 'success']);
        // }
    }

    public function infMsgResult(Request $request, $id)
    {

        require_once '/home/.../dms/test-InfMsgReceiver/api-rtgk.class.php';
        require_once '/home/.../fleita/HSM.class.php';
        //require_once '/home/.../dms/InfMsgReceiver/sendDms.php'; //Only for prod!!!
        require_once '/home/.../dms/test-InfMsgReceiver/test-receiver.php';
        if (!$dms = Dms::find($id)) {
            return response()->json(["Error" => "ID [" . $id . "] not found in DB"]);
        }
        if ($request->status == "new") {
            //$dms->save();
            return response()->json(['status' => 'failed'], 400);
        }

        $dms->status = $request->status;
        $dms->note = $request->note;
        $dms->is_mother_address = $request->is_mother_address;
        $dms->user_id = $request->user_id;
        $dms->organization_id = $request->organization_id;
        $dms->version = $request->version;

        // $this->validate($request, [
        //     'is_mother_address' => 'filled',
        //     'status' => 'filled',
        //     'note' => 'filled',
        //     'user_id' => 'filled',
        //     'organization_id' => 'filled',
        //     'version' => 'filled',
        // ]);
        // $dms = Dms::find($id);
        // if ($dms->fill($request->all())->save()) {
        //     return response()->json(['status' => 'success'],200);
        // }else{
        //     return response()->json(['status' => 'failed'], 400);
        // }

        $camel = new \RtgkApi(
            "",
            "",
            "http://"
        );
        $fio = json_decode($camel->getFIO($dms->citizen_id), false);




        $result = new Result;
        $result->requestID = Str::uuid();
        $result->requestTime = null;
        $result->sourceRequestID = trim($dms->id);
        //$regAdress = $camel->getRegistration($dms->citizen_id);

        if ($dms->status == "successfully") {

            $exit = ["status" => $dms->status, "text" => $fio->lastName . " " . $fio->firstName . " " . $fio->middleName . "; " . $request->note];
            $result->requestData = base64_encode(json_encode($exit));
            $result->$dms->status;

            $bearer                 = "eyJraWQiOiJrZXlJZCMzIiwidHlwIjoiSldUIiwiYWxnIjoiRVMyNTYifQ.eyJzdWIiOiJQb3N0bWFuQ2lwaGVyQ2FhUyIsIm5iZiI6MTYxNjc2ODQ4MCwic2NvcGUiOlsiVU9TX0FVVEhPUklaRURfQ0xJRU5UIl0sImlzcyI6Imdpb2NQcm9kIiwic2ZuIjoiUG9zdG1hbkNpcGhlckNhYVMiLCJleHAiOjE2MjIwMjc5NzIsImlhdCI6MTYxNjc2ODQ4MCwianRpIjoiYzhkOGE1MTItM2ExYy00OTI2LWE4OTctNTQ2YWQ0Yjg4ZmNiIn0.HU3CEBLF51NnVStK0JpFaNT3e6dKd6x1Fm78dH9tdyjSQnbKL7e6flqojevOz_5fl-SJuCtQXaAH14qSHgLOlg";
            $hsm = new \HSM($result->requestData, $bearer, "cihsm://gioc-site-1/shard-1.19b725e", "111113111113");
            if (empty($hsm)) {
                echo "\n\t\e[31m>DS not set.<\n";
                //throw new Exception('Цифровий підпис не отримано.'); exit();
            }

            $result->requestDataSign = $hsm->DSHash;
        } elseif ($dms->status == "denied") {
            if (!$fio) {
                $camel = json_decode($dms->data, false);
                $fio->firstName     =   $camel["applicantInfo"]["foaf_givenName"];
                $fio->middleName    =   isset($camel["applicantInfo"]["person_patronymicName"]) ? $camel["applicantInfo"]["person_patronymicName"] : "";
                $fio->lastName      =   $camel["applicantInfo"]["foaf_familyName"];
            }

            $exit = ["status" => $dms->status, "text" => $fio->lastName . " " . $fio->firstName . " " . $fio->middleName . "; Відмова: " . $dms->note];
            $result->requestData = base64_encode(json_encode($exit));
            $result->$dms->status;

            $bearer                 = "eyJraWQiOiJrZXlJZCMzIiwidHlwIjoiSldUIiwiYWxnIjoiRVMyNTYifQ.eyJzdWIiOiJQb3N0bWFuQ2lwaGVyQ2FhUyIsIm5iZiI6MTYxNjc2ODQ4MCwic2NvcGUiOlsiVU9TX0FVVEhPUklaRURfQ0xJRU5UIl0sImlzcyI6Imdpb2NQcm9kIiwic2ZuIjoiUG9zdG1hbkNpcGhlckNhYVMiLCJleHAiOjE2MjIwMjc5NzIsImlhdCI6MTYxNjc2ODQ4MCwianRpIjoiYzhkOGE1MTItM2ExYy00OTI2LWE4OTctNTQ2YWQ0Yjg4ZmNiIn0.HU3CEBLF51NnVStK0JpFaNT3e6dKd6x1Fm78dH9tdyjSQnbKL7e6flqojevOz_5fl-SJuCtQXaAH14qSHgLOlg";
            $hsm = new \HSM($result->requestData, $bearer, "cihsm://gioc-site-1/shard-1.19b725e", "111113111113");
            if (empty($hsm)) {
                echo "\n\t\e[31m>DS not set.<\n";
                //throw new Exception('Цифровий підпис не отримано.'); exit();
            }

            $result->requestDataSign = $hsm->DSHash;
        }


        $resp = callPersonInfoService((string) $result->requestID, $result->sourceRequestID, $result->requestData, $result->requestDataSign);

        if (is_object($resp)) {
            print_r($resp->status . " => ALL OK\n\n");
            $dms->processed = true;
            $dms->save();
        } else {
            print_r("No respons => Error...\n\n");
            print_r($resp);
        }


        return response()->json($exit);
    }

    public function infMsgTest(Request $request, $id)
    {
        require_once '/home/.../dms/test-InfMsgReceiver/api-rtgk.class.php';
        require_once '/home/.../fleita/HSM.class.php';
        require_once '/home/.../dms/test-InfMsgReceiver/test-receiver.php';
        if (!$dms = Dms::find($id)) {
            return response()->json(["Error" => "ID [" . $id . "] not found in DB"]);
        }
        $camel = new \RtgkApi(
            "",
            "",
            "http://"
        );
        $fio = json_decode($camel->getFIO($dms->citizen_id), false);

        $result = new Result;
        $result->requestID = Str::uuid();
        $result->requestTime = null;
        $result->sourceRequestID = trim($dms->id);
        //$regAdress = $camel->getRegistration($dms->citizen_id);
        if ($dms->status == "successfully") {
            $exit = ["status" => $dms->status, "text" => $fio->lastName . " " . $fio->firstName . " " . $fio->middleName . "; " . $request->note];
            $result->requestData = base64_encode(json_encode($exit));

            $bearer                 = "eyJraWQiOiJrZXlJZCMzIiwidHlwIjoiSldUIiwiYWxnIjoiRVMyNTYifQ.eyJzdWIiOiJQb3N0bWFuQ2lwaGVyQ2FhUyIsIm5iZiI6MTYxNjc2ODQ4MCwic2NvcGUiOlsiVU9TX0FVVEhPUklaRURfQ0xJRU5UIl0sImlzcyI6Imdpb2NQcm9kIiwic2ZuIjoiUG9zdG1hbkNpcGhlckNhYVMiLCJleHAiOjE2MjIwMjc5NzIsImlhdCI6MTYxNjc2ODQ4MCwianRpIjoiYzhkOGE1MTItM2ExYy00OTI2LWE4OTctNTQ2YWQ0Yjg4ZmNiIn0.HU3CEBLF51NnVStK0JpFaNT3e6dKd6x1Fm78dH9tdyjSQnbKL7e6flqojevOz_5fl-SJuCtQXaAH14qSHgLOlg";
            $hsm = new \HSM($result->requestData, $bearer, "cihsm://gioc-site-1/shard-1.19b725e", "111113111113");
            if (empty($hsm)) {
                echo "\n\t\e[31m>DS not set.<\n";
                //throw new Exception('Цифровий підпис не отримано.'); exit();
            }

            $result->requestDataSign = $hsm->DSHash;
        } elseif ($dms->status == "denied") {
            $exit = ["status" => $dms->status, "text" => $fio->lastName . " " . $fio->firstName . " " . $fio->middleName . "; Відмова: " . $dms->note];
            $result->requestData = base64_encode(json_encode($exit));

            $bearer                 = "eyJraWQiOiJrZXlJZCMzIiwidHlwIjoiSldUIiwiYWxnIjoiRVMyNTYifQ.eyJzdWIiOiJQb3N0bWFuQ2lwaGVyQ2FhUyIsIm5iZiI6MTYxNjc2ODQ4MCwic2NvcGUiOlsiVU9TX0FVVEhPUklaRURfQ0xJRU5UIl0sImlzcyI6Imdpb2NQcm9kIiwic2ZuIjoiUG9zdG1hbkNpcGhlckNhYVMiLCJleHAiOjE2MjIwMjc5NzIsImlhdCI6MTYxNjc2ODQ4MCwianRpIjoiYzhkOGE1MTItM2ExYy00OTI2LWE4OTctNTQ2YWQ0Yjg4ZmNiIn0.HU3CEBLF51NnVStK0JpFaNT3e6dKd6x1Fm78dH9tdyjSQnbKL7e6flqojevOz_5fl-SJuCtQXaAH14qSHgLOlg";
            $hsm = new \HSM($result->requestData, $bearer, "cihsm://gioc-site-1/shard-1.19b725e", "111113111113");
            if (empty($hsm)) {
                echo "\n\t\e[31m>DS not set.<\n";
                //throw new Exception('Цифровий підпис не отримано.'); exit();
            }

            $result->requestDataSign = $hsm->DSHash;
        }
        //        $resp = callPersonInfoService((string) $result->requestID,$result->sourceRequestID,$result->requestData,$result->requestDataSign);
        //print_r($resp);
        //        if (is_object($resp)){
        //            print_r($resp->status ." => ALL OK\n\n");
        //        }else{
        //            print_r("No respons => Error...\n\n");
        //            print_r($resp);
        //        }

        // $this->validate($request, [
        //     'requestID' => 'filled',
        //     'requestTime' => 'filled',
        //     'sourceRequestID' => 'filled',
        //     'requestData' => 'filled',
        //     'requestDataSign' => 'filled'
        // ]);


        return response()->json($exit);
    }

    public function infFake(Request $request)
    {
        //        dd($request->all());
        require_once '/home/.../dms/test-InfMsgReceiver/api-rtgk.class.php';
        require_once '/home/.../fleita/HSM.class.php';
        //require_once '/home/.../dms/InfMsgReceiver/sendDms.php'; //Only for prod!!!
        require_once '/home/.../dms/test-InfMsgReceiver/test-receiver.php';

        $camel = new \RtgkApi(
            "",
            "",
            "http://"
        );
        $fio = json_decode($camel->getFIO($request->input('citizen_id')), false);

        $result = new Result;
        $result->requestID = generate_uuid();
        $result->requestTime = null;
        $result->sourceRequestID = trim($request->input('citizen_id'));
        //$regAdress = $request->input('reg_address');
        if ($request->input('status') == "successfully") {
            $exit = ["status" => $request->input('status'), "text" => $request->input('surname') . " " . $request->input('username') . " " . $request->input('patronic') . "; " . $request->note];
            $result->requestData = base64_encode(json_encode($exit));

            $bearer                 = "eyJraWQiOiJrZXlJZCMzIiwidHlwIjoiSldUIiwiYWxnIjoiRVMyNTYifQ.eyJzdWIiOiJQb3N0bWFuQ2lwaGVyQ2FhUyIsIm5iZiI6MTYxNjc2ODQ4MCwic2NvcGUiOlsiVU9TX0FVVEhPUklaRURfQ0xJRU5UIl0sImlzcyI6Imdpb2NQcm9kIiwic2ZuIjoiUG9zdG1hbkNpcGhlckNhYVMiLCJleHAiOjE2MjIwMjc5NzIsImlhdCI6MTYxNjc2ODQ4MCwianRpIjoiYzhkOGE1MTItM2ExYy00OTI2LWE4OTctNTQ2YWQ0Yjg4ZmNiIn0.HU3CEBLF51NnVStK0JpFaNT3e6dKd6x1Fm78dH9tdyjSQnbKL7e6flqojevOz_5fl-SJuCtQXaAH14qSHgLOlg";
            $hsm = new \HSM($result->requestData, $bearer, "cihsm://gioc-site-1/shard-1.19b725e", "111113111113");
            if (empty($hsm)) {
                echo "\n\t\e[31m>DS not set.<\n";
                //throw new Exception('Цифровий підпис не отримано.'); exit();
            }

            $result->requestDataSign = $hsm->DSHash;
        } elseif ($request->input('status')  == "denied") {
            $exit = ["status" => $request->input('status'), "text" => $request->input('surname') . " " . $request->input('username') . " " . $request->input('patronic') . "; Відмова: " . $request->input('note')];
            $result->requestData = base64_encode(json_encode($exit));

            $bearer                 = "eyJraWQiOiJrZXlJZCMzIiwidHlwIjoiSldUIiwiYWxnIjoiRVMyNTYifQ.eyJzdWIiOiJQb3N0bWFuQ2lwaGVyQ2FhUyIsIm5iZiI6MTYxNjc2ODQ4MCwic2NvcGUiOlsiVU9TX0FVVEhPUklaRURfQ0xJRU5UIl0sImlzcyI6Imdpb2NQcm9kIiwic2ZuIjoiUG9zdG1hbkNpcGhlckNhYVMiLCJleHAiOjE2MjIwMjc5NzIsImlhdCI6MTYxNjc2ODQ4MCwianRpIjoiYzhkOGE1MTItM2ExYy00OTI2LWE4OTctNTQ2YWQ0Yjg4ZmNiIn0.HU3CEBLF51NnVStK0JpFaNT3e6dKd6x1Fm78dH9tdyjSQnbKL7e6flqojevOz_5fl-SJuCtQXaAH14qSHgLOlg";
            $hsm = new \HSM($result->requestData, $bearer, "cihsm://gioc-site-1/shard-1.19b725e", "111113111113");
            if (empty($hsm)) {
                echo "\n\t\e[31m>DS not set.<\n";
                //throw new Exception('Цифровий підпис не отримано.'); exit();
            }

            $result->requestDataSign = $hsm->DSHash;
        }
        //        dd($result);


        $resp = callPersonInfoService($result->requestID, $result->sourceRequestID, $result->requestData, $result->requestDataSign);
        print_r($resp);
        if (is_object($resp)) {
            print_r($resp->status . " => ALL OK\n\n");
        } else {
            print_r("No respons => Error...\n");
            print_r($resp);
        }

        // $this->validate($request, [
        //     'requestID' => 'filled',
        //     'requestTime' => 'filled',
        //     'sourceRequestID' => 'filled',
        //     'requestData' => 'filled',
        //     'requestDataSign' => 'filled'
        // ]);


        return response()->json($exit);
    }
     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function Fupdate(Request $request, $id)
    {
        sleep(4);
        return response()->json($request);
    }

    /**
     * Display a listing of the resource2.
     *
     * @return \Illuminate\Http\Response
     */
    public function Findex(Request $request)
    {
        error_reporting(-1);
        // DB::
        // select('my_rtg.*')->
        // //leftJoin('temp.my_dms_camel', 'my_dms_camel.id', '=', 'temp.my_rtg.id')->
        // //Join('temp.my_dms_camel', 'temp.my_rtg.sourceid', '=', 'temp.my_dms_camel.id')->
        // // orderBy('id', 'desc')->
        // // where('dms_status', '20')->
        // // skip(0)->
        // // take(5)->
        // paginate(15)->
        // get()
        //return response("qweqwe");
        $result = Dms::query();

        if (!empty($request->filter["type"])) {
            $arrayType = explode(',',$request->filter["type"]);
            if(count($arrayType)>1){
                dd($arrayType);

            }else {
                if(mb_strtolower($arrayType[0]) == "e14"){
                    $result =   $result->where('type','E14-4')->
                               orwhere('type','E14-10');
                }

                if(mb_strtolower($request->filter["type"]) == "er"){
                    $result =   $result->where('type','ER-4')->
                               orwhere('type','ER-10')->
                               orwhere('type','ER-11');
                }

                if (mb_strtoupper($request->filter["type"]) == "ER-11") {
                    $result =   $result->where('type','ER-11');
                                //->orwhere('type','E14-10');
                   if (!empty($request->filter["districtId"])) {
                        $result =   $result->where('out_district_id', trim($request->filter["districtId"]));
                   }
                }

            }
        }

        if (!empty($request->filter["status"])) {
            $result = $result->where('status', $request->filter["status"]);
        }

        if (!empty($request->filter['created|>='])) {
            if (empty($request->filter['created|<='])) {
                $end_date = Carbon::now('Europe/Kiev')->toDateTimeString();
            } else {
                $end_date = Carbon::parse(trim($request->filter['created|<='], ":999"))->endOfDay()->toDateTimeString();
            }
            $start_date = Carbon::parse($request->filter["created|>="])->startOfDay()->toDateTimeString();

            $result = $result->whereBetween('created', [$start_date, $end_date]);

        }

        if (!empty($request->filter["districtId"])) {
            $result = $result->where(function ($query) use ($request) {
                            $query->whereNotIn('type', ['ER-10','ER-4'])
                                ->where('out_district_id', trim($request->filter["districtId"]))
                                //->whereNotIn('type', ['ER-10','ER-4'])
                                //->where('district_id', trim($request->filter["districtId"]))
                                ;
                        })->where(function ($query) use ($request) {
                            $query->whereIn('type', ['ER-10','ER-4'])
                                ->orWhere('district_id', trim($request->filter["districtId"]))
                                //->where('district_id', trim($request->filter["districtId"]))
                                ;
                        });
            // $result = $result->where(function ($query) use ($request) {
            //                 $query->whereNotIn('type', ['ER-11'])
            //                     ->where('district_id', trim($request->filter["districtId"]));
            //             });
        }

        if ($request->page) {
            //dd($request->page);
            $result = $result->
            select(
                's_id'
                ,'id'
                , "type"
                //, "data"
                , 'normalized_data'
                , 'requestid_dms'
                , 'citizen_id'
                , 'statement_id'
                , 'register_record_id'
                , 'is_mother_address'
                , 'out_original_address'
                , 'original_address'
                , 'status'
                , 'note'
                , 'user_id'
                , 'organization_id'
                , "locked"
                , 'created'
                , 'updated'
                , "version"
                , 'processed'
                , 'last_name'
                , 'first_name'
                , 'middle_name'
                , 'date_of_birth'
                , 'created'
                , 'updated'
            )->
            orderBy('s_id', 'desc')->
            where('locked', false)->
            simplePaginate(20);

            return response($result->items());

        } else {
            $result = $result->
            select(
                's_id'
                ,'id'
                , "type"
                //, "data"
                , 'normalized_data'
                , 'requestid_dms'
                , 'citizen_id'
                , 'statement_id'
                , 'register_record_id'
                , 'is_mother_address'
                , 'out_original_address'
                , 'original_address'
                , 'status'
                , 'note'
                , 'user_id'
                , 'organization_id'
                , "locked"
                , 'created'
                , 'updated'
                , "version"
                , 'processed'
                , 'last_name'
                , 'first_name'
                , 'middle_name'
                , 'date_of_birth'
            )->
            orderBy('s_id', 'desc')->
            where('locked', false)->
            skip(0)->
            //take(100)->
            get();
            //->where('processed', false)
            //dd($result);

            return response($result);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function Fshow($id)
    {
        //require_once '/home/.../dms/InfMsgReceiver/processedDms.class.php';
        //$nor = new \pracessedDms(null,null,);
        //$nor->updateById($id);
        $todo = DmsTest::first()->where('s_id', $id)->where('locked', false)->get();
        if (isset($todo[0])) {
            return response($todo[0]);
        } else {
            return response()->json(['Результат' => 'Данні з ID: \'' . $id . '\'  відсутні.'], 404);
        }
    }

}
