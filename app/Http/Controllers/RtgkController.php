<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dms;
use App\Models\DmsTest;
use App\Models\Result;
use App\Models\Control;
use Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use TelegramBot\Api\BotApi;
use Illuminate\Support\Facades\DB;

class RtgkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $result = Dms::query();



        // if (!empty($request->filter["districtId"])) {
        //     if (!empty($request->filter['created|>='])) {
        //         if (empty($request->filter['created|<='])) {
        //             $end_date = Carbon::now('Europe/Kiev')->toDateTimeString();
        //         } else {
        //             $end_date = Carbon::parse(trim($request->filter['created|<='], ":999"))->endOfDay()->toDateTimeString();
        //         }
        //         $start_date = Carbon::parse($request->filter["created|>="])->startOfDay()->toDateTimeString();
        //         if (!empty($request->filter["status"])) {
        //             $result = $result->where('district_id', trim($request->filter["districtId"]))->where('locked', false)
        //                 ->where('status', $request->filter["status"])
        //                 ->whereBetween('created', [$start_date, $end_date])
        //                 //->orderBy('s_id', 'desc')->where('locked', false)->where('processed', false)
        //                 ->orWhere(function ($query) use ($request, $start_date, $end_date) {
        //                     $query->where('type', 'ER-11')
        //                         ->where('out_district_id', trim($request->filter["districtId"]))
        //                         ->whereBetween('created', [$start_date, $end_date])
        //                         ->where('status', $request->filter["status"])
        //                         ->orderBy('s_id', 'desc')->where('locked', false);
        //                 });
        //         } else {
        //             $result = $result->where('district_id', trim($request->filter["districtId"]))->where('locked', false)
        //                 ->whereBetween('created', [$start_date, $end_date])
        //                 ->orWhere(function ($query) use ($request, $start_date, $end_date) {
        //                     $query->where('type', 'ER-11')
        //                         ->where('out_district_id', trim($request->filter["districtId"]))
        //                         ->whereBetween('created', [$start_date, $end_date])
        //                         ->orderBy('s_id', 'desc')->where('locked', false);
        //                 });
        //         }
        //     } else {
        //         if (!empty($request->filter["status"])) {
        //             $result = $result->where('district_id', trim($request->filter["districtId"]))
        //                 ->where('status', $request->filter["status"])
        //                 ->orWhere(function ($query) use ($request) {
        //                     $query->where('type', 'ER-11')
        //                         ->where('out_district_id', trim($request->filter["districtId"]))
        //                         ->where('status', $request->filter["status"])
        //                         ->orderBy('s_id', 'desc')->where('locked', false);
        //                 });
        //         } else {
        //             $result = $result->where('district_id', trim($request->filter["districtId"]))
        //                 ->orWhere(function ($query) use ($request) {
        //                     $query->where('type', 'ER-11')
        //                         ->where('out_district_id', trim($request->filter["districtId"]));
        //                 })
        //                 ->orWhere(function ($query) use ($request) {
        //                     $query->where('type', 'ER-10')
        //                         ->where('district_id', trim($request->filter["districtId"]));
        //                 });
        //         }
        //     }
        // } elseif (!empty($request->filter["created|>="])) {
        //     if (empty($request->filter["created|<="])) {
        //         $end_date = Carbon::now('Europe/Kiev')->toDateTimeString();
        //     } else {
        //         $end_date = Carbon::parse(trim($request->filter['created|<='], ":999"))->endOfDay()->toDateTimeString();
        //     }
        //     $start_date = Carbon::parse($request->filter["created|>="])->startOfDay()->toDateTimeString();
        //     if (!empty($request->filter["status"])) {
        //         $result = $result->whereBetween('created', [$start_date, $end_date])->where('status', $request->filter["status"]);
        //     } else {
        //         $result = $result->whereBetween('created', [$start_date, $end_date]);
        //     }
        // } elseif (!empty($request->filter["status"])) {
        //     $result = $result->where('status', $request->filter["status"]);
        // }


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
                            $query->where('type', 'ER-11')
                                ->where('out_district_id', trim($request->filter["districtId"]))
                                ->orWhere('district_id', trim($request->filter["districtId"]));
                        });
        }




        // if (!empty($request->filter["created_from"])) {
        //     if (empty($request->filter["created_to"])) {
        //         $end_date = Carbon::now('Europe/Kiev')->toDateTimeString();
        //     } else {
        //         $end_date = Carbon::parse($request->filter["created_to"])->endOfDay()->toDateTimeString();
        //     }
        //     $start_date = Carbon::parse($request->filter["created_from"])->startOfDay()->toDateTimeString();
        //     $result = $result->whereBetween('created', [$start_date, $end_date]);
        // }
        // if (!empty($request->filter["status"])) {
        //     $result = $result->where('status', $request->filter["status"]);
        // }
        // if (!empty($request->filter["districtId"])) {
        //     $result = $result->where('district_id', trim($request->filter["districtId"]));
        //     $result = $result->orWhere('type', 'ER-11')
        //         ->where('out_district_id', trim($request->filter["districtId"]));
        // }









        if ($request->page) {
            //dd($request->page);
            $result = $result->orderBy('s_id', 'desc')->where('locked', false)->simplePaginate(20);
            return response($result->items());
        } else {
            $result = $result->
            orderBy('s_id', 'desc')->
            where('locked', false)->
            skip(0)->
            take(100)->
            get(); //->where('processed', false)
            return response($result);
        }
        //dd($result);


        // } elseif ($request->search) {
        //     $dms = Dms::orderBy('s_id', 'desc')->firstWhere('id', $request->search);
        //     if (!isset($dms->data)) {
        //         return response()->json(["result" => "No data."]);
        //     }
        //     return response()->json($dms->data);
        // }
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
        require_once '/home/.../dms/InfMsgReceiver/sendDms.php'; //Only for prod!!!
        //require_once '/home/.../dms/test-InfMsgReceiver/test-receiver.php';

        $tgToken = "";
        $chatId = "";

        try {
            $bot = new BotApi($tgToken);
        } catch (\Throwable $e) {
            //return response()->json(["Error" => "ID [" . $bot . "] not found in DB"], 500);
        }

        //var_dump($bot);
        // $bot->sendMessage(
        //     $chatId,
        //     "❌Помилка выдправки:\n[" . json_encode($fault) . "]\n"
        // );


        if (!$dms = Dms::find($id)) {

            return response()->json(["Error" => "ID [" . $id . "] not found in DB"], 500);
        } else {
            //return response()->json($request, 200);
        }

        $markerForBot =  "⚠️";
        //$exitForBot = "";

        if ($dms->processed == true) {
            $botMessage = "⚠️Data <b>send</b>:
        Спроба надіслати вже оброблені данні.
        Запит№: [ " . $id . " ]
        ДМС№: [ " . $dms->requestid_dms . " ]
        <b>Дія№</b>: [ <code>" . $dms->id . "</code> ]
        processed: [ " . json_encode($dms->processed) . " ]
        status: [ " . $request->status . " ] ".$markerForBot."
        note: [ <code>" . $request->note . "</code> ]
        ID користувача№: [ <code>{$fioEmploee}</code> ]
    ";
            $bot->sendMessage($chatId, $botMessage, "html", true, null, null, false);
            return response()->json(['Результат' => 'Данні вже надіслано.'], 500);
        }

        // if ($dms->status == "successfully") {
        // }



        if ($dms->status == "new") {

            if ($request->status == "new") {

                $this->validate($request, [
                    'is_mother_address' => 'filled',
                    'status' => 'filled',
                    'note' => 'filled',
                    'user_id' => 'filled',
                    'organization_id' => 'filled',
                    'version' => 'filled',
                    'out_original_address' => 'filled',
                    'out_building_id' => 'filled',
                    'out_residence_id' => 'filled',
                    'out_street_id' => 'filled',
                    'out_district_id' => 'filled',
                    'out_locality_id' => 'filled',
                    'out_country_id' => 'filled',
                    'original_addressdress' => 'filled',
                    'residence_id' => 'filled',
                    'building_id' => 'filled',
                    'street_id' => 'filled',
                    'district_id' => 'filled',
                    'locality_id' => 'filled',
                    'country_id' => 'filled',
                ]);

                $request->merge(['version' => ++$dms->version]);
                if ($dms->fill($request->all())->save()) {
                    //$dms->version = ++$dms->version;
                    //$dms->save();
                    return response()->json(['Результат' => 'Данні збережено.'], 200);
                } else {
                    return response()->json(['Помилка відправки' => 'Необхідно скинути кеш, оновити сторінку та спробувати ще раз.'], 500);
                }
            }
        }


        $camel = new \RtgkApi(
            "",
            "",
            "http://"
        );
        $request->user_id = $request->user_id ?? "";
        if(!empty($request->user_id)){
            $fioEmploeeArray = json_decode($camel->getFIO($request->user_id), true);
            $fioEmploee = $fioEmploeeArray["lastName"]." ".$fioEmploeeArray["firstName"]." ".$fioEmploeeArray["middleName"];
        }else{
            $fioEmploee = "";
            return response()->json(['Помилка відправки' => 'Помилка данних.Спробуйте скинути кеш браузера та оновити сторінку.'], 500);
        }

        $result                     =   new Result;
        $result->requestID          =   $dms->s_id; //generate_uuid();
        $objDateTime                =   new \DateTime('NOW');
        $result->requestTime        =   $objDateTime->format('c');
        $result->sourceRequestID    =   (int)$dms->requestid_dms;

        if (empty($dms->citizen_id)) {
            if ($request->status == "denied") {
                $camel = $dms->data;
                $fio = (object)[
                    "firstName"     =>   $camel->applicantInfo->foaf_givenName,
                    "middleName"    =>   isset($camel->applicantInfo->person_patronymicName) ? $camel->applicantInfo->person_patronymicName : "",
                    "lastName"      =>   $camel->applicantInfo->foaf_familyName
                ];
            } else {
                return response()->json(['Помилка відправки' => 'Відсутня картка ЕКГ користувача.ID: ' . $dms->id], 500);
            }
        } else {
            if ($request->status == "denied") {
                $camel = $dms->data;
                $fio = (object)[
                    "firstName"     =>   $camel->applicantInfo->foaf_givenName,
                    "middleName"    =>   isset($camel->applicantInfo->person_patronymicName) ? $camel->applicantInfo->person_patronymicName : "",
                    "lastName"      =>   $camel->applicantInfo->foaf_familyName
                  ];
            }else{
                //$regAdress              =   $camel->getRegistration($dms->citizen_id);
                $fio = json_decode($camel->getFIO($dms->citizen_id), false);
            }
        }

        $bearer                     = "eyJraWQiOiJrZXlJZCMzIiwidHlwIjoiSldUIiwiYWxnIjoiRVMyNTYifQ.eyJzdWIiOiJnaW9jLWRldiIsIm5iZiI6MTYxNTQ3NTQ3MSwic2NvcGUiOlsiVU9TX0FVVEhPUklaRURfQ0xJRU5UIl0sImlzcyI6Imdpb2NQcm9kIiwic2ZuIjoiZ2lvYy1kZXYiLCJleHAiOjE2NDcwMzI0MjMsImlhdCI6MTYxNTQ3NTQ3MSwianRpIjoiODc0ZjI4MzctNjRjZi00OTQ4LThlZmQtMjFlYWYyYzE5NTdhIn0.wPW31MfpYCn5s2PkGtNOmiTrmqEcFTlkPWVKr_ZsmEfX2yKMBdek34XDpoAcfe_6UqXGnHlttabbvU0vhNph4g";



        $maskLastName = $fio->lastName ? preg_replace("/(?!^).(?!$)/u", "*", $fio->lastName) : "";
        $maskFirstName = $fio->firstName ? preg_replace("/(?!^).(?!$)/u", "*", $fio->firstName) : "";
        $maskMiddleName = $fio->middleName ? preg_replace("/(?!^).(?!$)/u", "*", $fio->middleName) : "";
        $exitForBot = "-- empty --";
        if ($request->status == "successfully") {
            $exit = [
                "status" => $request->status, "text" =>
                $fio->lastName
                    . " "
                    . $fio->firstName
                    . " "
                    . $fio->middleName
                    . "; "
                    . $request->note
                ];
            $markerForBot =  "🟢";
            $exitForBot = [
                "status" => $request->status, "text" =>
                    $maskLastName
                    . " "
                    . $maskFirstName
                    . " "
                    .  $maskMiddleName
                    . "; "
                    . $request->note
                ];
                $result->requestData    =   base64_encode(json_encode($exit));
            } elseif ($request->status == "denied") {

                $exit                   =   [
                    "status" => $request->status, "text" =>
                    $fio->lastName
                    . " "
                    . $fio->firstName
                    . " "
                    . $fio->middleName
                    . "; Відмова: "
                    . $request->note
                ];
                $markerForBot =  "🔴";
                $exitForBot                   =   [
                    "status" => $request->status, "text" =>
                    $maskLastName
                    . " "
                    . $maskFirstName
                    . " "
                    .  $maskMiddleName
                    . "; Відмова: "
                    . $request->note
                ];

                $result->requestData    =   base64_encode(json_encode($exit));
            } else {
                return response()->json(['Wrong request status' => 'status: ' . $request->status], 500);
            }

            $botMessage = "⬜️[ <code>" . $dms->id . "</code> ]
        status: [ " . $request->status . " ] ".$markerForBot."
        ID користувача№: [ <code>{$fioEmploee}</code> ]
        Тіло запиту: [ <code>" . json_encode($exitForBot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</code> ]
        ";
            $messageId = $bot->sendMessage($chatId, $botMessage, "html", true, null, null, false)->getMessageId();

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

        $result->requestDataSign    =   $hsm->DSHash;

        // $resp = (object)["status"=>"10"];
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
        try {
            $resp                       =   callPersonInfoService(
                (string) $result->requestID,
                $result->sourceRequestID,
                $result->requestData,
                $result->requestDataSign
            );
        } catch (\Exception $e) {
            // error_log(
            //     date('Y-m-d H:m:s P')
            //         . "[s_id:"
            //         . $dms->s_id
            //         . "]-"
            //         . "[requestid_dms:"
            //         . $dms->requestid_dms
            //         . "]-"
            //         . "[id:"
            //         . $dms->id
            //         . "]\n"
            //         . "Error="
            //         . json_encode($th)
            //         . "\n\n",
            //     3,
            //     "/home/.../dms/InfMsgReceiver/sendDms.log"
            // );
            $botMessageErr = "🟥Data <b>send</b>:
        Помилка відправки даних!
        Запит№: [ " . $id . " ]
        ДМС№: [ " . $dms->requestid_dms . " ]
        <b>Дія№</b>: [ <code>" . $dms->id . "</code> ]
        processed: [ " . json_encode($dms->processed) . " ]
        status: [ " . $request->status . " ] ".$markerForBot."
        note: [ <code>" . $request->note . "</code> ]
        ID користувача№: [ <code>{$fioEmploee}</code> ]
        Тіло запиту: [ <code>" . json_encode($exitForBot) . "</code> ]
    ";
    $bot->editMessageText($chatId, $messageId, $botMessageErr, "html");
            //$bot->sendMessage($chatId, $botMessageErr, "html", true, null, null, false);
            return response()->json(['Error' => 'Trouble with sending', 'callPersonInfoService error' => $e->getMessage()], 500);
        }

        if ($resp) {


            error_log(
                date('Y-m-d H:m:s P')
                    . "[s_id:"
                    . $dms->s_id
                    . "]-"
                    . "[requestid_dms:"
                    . $dms->requestid_dms
                    . "]-"
                    . "[id:"
                    . $dms->id
                    . "]\n"
                    . "Result="
                    . json_encode($resp)
                    . "\n\n",
                3,
                "/home/.../dms/InfMsgReceiver/sendDms.log"
            );
            if (!isset($resp["status"])) {
                try {
                    $botMessageErr2 = "🟥[ <code>" . $dms->id . "</code> ] | <b>Помилка відправки даних через Трембіту🎷!</b>❌
           ➡️Запит№: [ " . $dms->id . " ]
           ➡️ДМС№: [ " . $dms->requestid_dms . " ]
           ➡️<b>Дія№</b>: [ <code>" . $dms->id . "</code> ]
           ℹ️status: [ " . $request->status . " ] ".$markerForBot."
           ℹ️note: [ <code>" . $request->note . "</code> ]
           👤ID користувача№: [ <code>" . $request->user_id . "</code> ] ({$fioEmploee})
           📝Тіло запиту: [ <code>" . json_encode($exitForBot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</code> ]
           🔴Код відмови: <code>Сервіс Трембіти не зміг надіслати запит🧧</code>
           🔴Текст відмови: <code>\"". json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ."\"</code> ";
                     $bot->editMessageText($chatId, $messageId, $botMessageErr2, "html");
                } catch (\Throwable $th) {
                     error_log(
                    date('Y-m-d H:m:s P')
                        . "[s_id:"
                        . $dms->s_id
                        . "]-"
                        . "[requestid_dms:"
                        . $dms->requestid_dms
                        . "]-"
                        . "[id:"
                        . $dms->id
                        . "]\n("
                        . $botMessageErr2
                        . ")\n("
                        . json_encode($th->getMessage(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        . ")\n\n",
                    3,
                    "/home/.../dms/InfMsgReceiver/sendDms.log"
                );
                }
                return response()->json(['Error' => 'Trembita send ERROR', 'Trembita error' => json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)], 500);
            }

            if ($resp["status"] == 10) {
                $result->response = $resp["status"];
                $result->response_text = isset($resp["statusText"]) ? $resp["statusText"] : "null";

                $this->validate($request, [
                    'is_mother_address' => 'filled',
                    'status' => 'filled',
                    'note' => 'filled',
                    'user_id' => 'filled',
                    'organization_id' => 'filled',
                    'version' => 'filled',
                    'out_original_address' => 'filled',
                    'out_building_id' => 'filled',
                    'out_residence_id' => 'filled',
                    'out_street_id' => 'filled',
                    'out_district_id' => 'filled',
                    'out_locality_id' => 'filled',
                    'out_country_id' => 'filled',
                    'original_addressdress' => 'filled',
                    'residence_id' => 'filled',
                    'building_id' => 'filled',
                    'street_id' => 'filled',
                    'district_id' => 'filled',
                    'locality_id' => 'filled',
                    'country_id' => 'filled',
                ]);

                $request->merge(['version' => ++$dms->version]);
                $dms->processed      =   true;
                $dms->fill($request->all())->save();
                $result->save();
                //$botMessageOk = "🟩[ <code>" . $dms->id . "</code> ] - ДМС отримав запит.";
                $botMessageOk = "🟩[ <code>" . $dms->id . "</code> ] | <b>ДМС отримав запит.</b>☑️
    status: [ <b>" . $request->status . "</b> ] ".$markerForBot."
    ID користувача№: [ <code>{$fioEmploee}</code> ]
    Тіло запиту: [ <code>" . json_encode($exitForBot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</code> ]
    ";
                try {
                    $bot->editMessageText($chatId, $messageId, $botMessageOk, "html");
                } catch (\Throwable $th) {
                     error_log(
                    date('Y-m-d H:m:s P')
                        . "[s_id:"
                        . $dms->s_id
                        . "]-"
                        . "[requestid_dms:"
                        . $dms->requestid_dms
                        . "]-"
                        . "[id:"
                        . $dms->id
                        . "]\n("
                        . $botMessageOk
                        . ")\n("
                        . json_encode($th->getMessage(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        . ")\n\n",
                    3,
                    "/home/.../dms/InfMsgReceiver/sendDms.log"
                );
                }
                //$bot->sendMessage($chatId, $botMessage, "html", true, null, null, false);
                return response()->json(['status_code' => 10, 'Data sended' => $exit], 200);
            } else {
                error_log(
                    date('Y-m-d H:m:s P')
                        . "[s_id:"
                        . $dms->s_id
                        . "]-"
                        . "[requestid_dms:"
                        . $dms->requestid_dms
                        . "]-"
                        . "[id:"
                        . $dms->id
                        . "]\n"
                        . "Result = REGECTED "
                        . "]\n"
                        . "status = "
                        . $resp["status"]
                        . "\nstatusText = "
                        . $resp["statusText"]
                        . "\n\n",
                    3,
                    "/home/.../dms/InfMsgReceiver/sendDms.log"
                );
                // $botMessage = "🟥[ <code>" . $dms->id . "</code> ] | <b>ДМС відхилив запит.</b>❌
                // Код відмови: <code>". $resp["status"]."</code>
                // Текст відмови: <code>\" ".$resp["statusText"]. "\"</code> ";
                $botMessageErr2 = "🟥[ <code>" . $dms->id . "</code> ] | <b>Помилка відправки даних!</b>❌
        ➡️Запит№: [ " . $dms->s_id . " ]
        ➡️ДМС№: [ " . $dms->requestid_dms . " ]
        ➡️<b>Дія№</b>: [ <code>" . $dms->id . "</code> ]
        ℹ️status: [ " . $request->status . " ] ".$markerForBot."
        ℹ️note: [ <code>" . $request->note . "</code> ]
        👤ID користувача№: [ <code>" . $request->user_id . "</code> ] ({$fioEmploee})
        📝Тіло запиту: [ <code>" . json_encode($exitForBot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</code> ]
        🔴Код відмови: <code>". $resp["status"]."</code>
        🔴Текст відмови: <code>\" ".$resp["statusText"]. "\"</code> ";
                $bot->editMessageText($chatId, $messageId, $botMessageErr2, "html");
                //$bot->sendMessage($chatId, $botMessage, "html", true, null, null, false);
                return response()->json(['result' => 'ДМС відхилив запит.Спробуйте ще раз пізніше.'.  " Текст відмови: \" ".$resp["statusText"]. "\" ", 'status' => $resp["status"]], 500);
            }
        }

        return response()->json(['Error' => 'Відправка на ДМС не здійснена.', 'Помилка відправки' => $resp], 500);
        //        return response()->json($exit);
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
