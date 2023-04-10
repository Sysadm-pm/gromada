<?php

namespace App\Http\Controllers;

date_default_timezone_set('Europe/Kyiv');

use Illuminate\Http\Request;
use App\Models\Dms;
use App\Models\DmsMessage;
use App\Models\DmsTest;
use App\Models\Register;
use App\Models\Notification;
use App\Models\Result;
use Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DateTime;
use TelegramBot\Api\BotApi;
use Illuminate\Support\Facades\DB;

class DmsMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *2022-09-01T12:09:39.599Z
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // require_once '/home/.../dms/InfMsgReceiver/api-rtgk.class.php';


        // try {
        //     $rtgk = new \RtgkApi(
        //         env('RTGK_BASIC'),
        //         env('RTGK_XSIGNATURE'),
        //         env('RTGK_URL')
        //     );
        // } catch (\Throwable $th) {
        //     return $th;
        // }

        try {
            //code...
            $result = DmsMessage::select([
                "id",
                "uuid",
                "register_record_id",
                "processed",
                "error",
                "msgcard",
                "msgnotes",
                "msgimages",
                "msgadbunit",
                "msgadbuser",
                "msgdate",
                "response_status",
                "response_note",
                "response_id",
                "created",
                "updated",
                "requestid",
                "payload",
                "status",
                "note",
                //"send_data",
                //"send_sign",
                "citizen_id",
                "organization_id",
                "user_id",
                "district_id",
                "district_name"
            ])
                ->orderBy('processed', 'asc')
                ->orderBy('id', 'asc')
                ->where("requestid", "!=", null)
                ->get();
        } catch (\Throwable $th) {
            return response($th, 500);
        }
        if ($request->count === "true") {
            try {
                return response()->json(["count" => count($result)]);
            } catch (\Throwable $th) {
                return response($th, 500);
            }
        }
        //$count = $result->count();

        //dd(count($result));
        //$stamp = (\DateTimeInterface::RFC3339, $result[0]->msgdate); // get unix timestamp
        //$time_in_ms = $stamp;

        //$date->getTimestamp();
        //dd(date_parse($result[0]->msgdate));
        //dd( [ $result[0]->msgdate, date('Y-m-d H:i:s.v', date_parse($result[0]->msgdate)) ] );
        //foreach ($result as $key => $value) {
        //    $message = DmsMessage::find($value->id);
        //    $data = json_decode($rtgk->getRegisterRecord($message->register_record_id, "id"))[0];
        //    $message->district_id   = $data->districtId->id;
        //    $message->district_name = $data->districtId->shortName;
        // $bigCamel = json_decode($message->msgcard);
        // if(isset($bigCamel->data)){
        //     $camel = $bigCamel->data;
        //     $message->register_record_id = intval( $camel->MsgCard->SourceID );
        //             $message->msgcard = json_encode($camel->MsgCard);//, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        //             $message->msgnotes = $camel->MsgNotes;
        //             $message->msgimages = $camel->MsgImages;
        //             $message->msgadbunit = $camel->MsgADBUnit;
        //             $message->msgadbuser = $camel->MsgADBUser;
        //             $message->msgdate = $camel->MsgDate;
        //             $message->requestid = $bigCamel->requestId;
        //             $message->payload = json_encode([
        //                 $bigCamel->requestId,
        //                 $bigCamel->client,
        //                 $bigCamel->dataSource,
        //                 $bigCamel->dataDestination,
        //                 $bigCamel->userid
        //             ]);
        //        $message->save();
        //return response( $message,200);
        // }else{
        //return response( "Nope!",200);
        // }

        //         //$message->response_status
        //         //$message->response_note
        //         //$message->response_id
        //$value->msgcard
        // }
        // $resultMessage = DmsMessage::where('id',">", 9)->where('id',"<", 15)->get();
        return response($result, 200);


        // return response($result,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $message = new DmsMessage;
        $message->payload = [
            "requestId" => $request->requestId,
            "full" => $request->input()
        ];
        $message->save();

        try {
            $message->register_record_id    = isset($message->payload->full->data->MsgCard->SourceID)         ?   $message->payload->full->data->MsgCard->SourceID          :   null;
            $message->requestid             = isset($message->payload->full->requestId)                       ?   $message->payload->full->requestId                        :   null;
            $message->citizen_id            = isset($message->payload->full->data->MsgCard->SourcePersID)     ?   $message->payload->full->data->MsgCard->SourcePersID      :   null;
            $message->msgcard               = isset($message->payload->full->data->MsgCard)                   ?   $message->payload->full->data->MsgCard                    :   null;
            $message->msgnotes              = isset($message->payload->full->data->MsgNotes)                  ?   $message->payload->full->data->MsgNotes                   :   null;
            // dd($value->payload->full->data->MsgNotes);
            $message->msgimages             = isset($message->payload->full->data->MsgImages)                 ?   $message->payload->full->data->MsgImages                  :   null;
            $message->msgadbunit            = isset($message->payload->full->data->MsgADBUnit)                ?   $message->payload->full->data->MsgADBUnit                 :   null;
            $message->msgadbuser            = isset($message->payload->full->data->MsgADBUser)                ?   $message->payload->full->data->MsgADBUser                 :   null;
            $message->msgdate               = isset($message->payload->full->data->MsgDate)                   ?   $message->payload->full->data->MsgDate                    :   null;
            $message->citizen_id = (Register::find($message->register_record_id))->citizen_id;
            require_once '/home/.../dms/InfMsgReceiver/api-rtgk.class.php';

            try {
                $rtgk = new \RtgkApi(
                    env('RTGK_BASIC'),
                    env('RTGK_XSIGNATURE'),
                    env('RTGK_URL')
                );
            } catch (\Throwable $th) {
                error_log(
                    $th,
                    3,
                    "/home/.../!test_scripts/msg_dms.log"
                );
            }
            $data = json_decode($rtgk->getRegisterRecord($message->register_record_id, "id"))[0];
            $message->district_id   = $data->districtId->id;
            $message->district_name = $data->districtId->shortName;
            $message->save();
        } catch (\Throwable $th) {
            error_log(
                $th,
                3,
                "/home/.../!test_scripts/msg_dms.log"
            );
        }
        try {
            $textus = "[" . $request->requestId . "] >>> \n"
                . date('Y-m-d H:i:s') . " [Begin]";

            function arrayToText($value, $textus = "", $tab = '')
            {
                $i = $tab ? $tab : null;
                $p = $i ? "\n" : "";
                $tab = "\t" . $i;
                $t = $i ? $tab : "";
                // $textus .= "\n";
                if (!is_string($value)) {
                    foreach ($value as $k1 => $v1) {
                        if (is_object($v1) or is_array($v1)) {
                            $textus .=  $p . $k1 . " =>> " . $t . " [ " . arrayToText($v1, '', $tab) . "]" . ($i ? "" : "\n");
                        } elseif (is_string($v1) or is_numeric($v1) or is_bool($v1) or is_null($v1)) {
                            $textus .=  ($i ? "\n" : "") . $t . $k1 . " => " . ($v1 ? $v1 : "null") . ($i ? "" : "\n");
                        } else {
                            $textus .=  "\n [[" . json_encode($k1) . "]] ===> " . json_encode($v1);
                        }
                    }
                } else {
                    // $textus .= $t;
                    // $textus .= $value;
                }

                $textus .= ($i ? "\n" : "") . $t;
                return $textus;
            }

            $textus = arrayToText($request->input(), $textus);

            error_log(
                $textus,
                3,
                "/home/.../!test_scripts/msg_dms.log"
            );
        } catch (\Throwable $th) {
            error_log(
                "[" . $request->requestId . "][ERROR] \n"
                    . date('Y-m-d H:i:s') . "\n" . $th .
                    "\n\n",
                3,
                "/home/.../!test_scripts/msg_dms.log"
            );
        }

        function isJson($string)
        {
            json_decode($string);
            // switch (json_last_error()) {
            //     case JSON_ERROR_NONE:
            //         echo ' - Ошибок нет';
            //     break;
            //     case JSON_ERROR_DEPTH:
            //         echo ' - Достигнута максимальная глубина стека';
            //     break;
            //     case JSON_ERROR_STATE_MISMATCH:
            //         echo ' - Некорректные разряды или несоответствие режимов';
            //     break;
            //     case JSON_ERROR_CTRL_CHAR:
            //         echo ' - Некорректный управляющий символ';
            //     break;
            //     case JSON_ERROR_SYNTAX:
            //         echo ' - Синтаксическая ошибка, некорректный JSON';
            //     break;
            //     case JSON_ERROR_UTF8:
            //         echo ' - Некорректные символы UTF-8, возможно неверно закодирован';
            //     break;
            //     default:
            //         echo ' - Неизвестная ошибка';
            //     break;
            // }
            return json_last_error() === JSON_ERROR_NONE;
        }

        $camel1 = isset($request->data)     ?   $request->data              :   null; //)?base64_decode($request->data):$request->data;
        $camel  = isJson($camel1)           ?   json_decode($camel1, false) :   null; //json_decode($request->data, false);

        if (!$camel) {
            $message->error = "Error.Empty data or wrong JSON.\n" . json_encode([$camel1, $camel]);
            $message->save();
            error_log(
                "[" . $request->requestId . "] "
                    . date('Y-m-d H:i:s') . " Error.\n" . json_encode([$camel1, $camel]) . "\n\n",
                3,
                "/home/.../!test_scripts/msg_dms.log"
            );
            return response("Error.Empty data or wrong JSON.\n" . json_encode([$camel1, $camel]), 500);
        }

        $client = isset($request->client)   ?   $request->client    :   "";
        $client = isJson($client)           ?   json_decode($client) :   "";

        $msgcard = isset($camel->MsgCard)   ?   $camel->MsgCard     :    null;
        //$msgcard = json_decode(json_encode($msgcard), false);
        //return response(200);//->json($request->all());

        try {
            error_log(
                "[camel] "
                    . date('Y-m-d H:i:s')
                    . "\n camel          => " . json_encode($camel)
                    . "\n msgcard        => " . json_encode($msgcard)
                    . "\n client         => " . json_encode($client)
                    . "\n SourceID       => " . $camel->MsgCard->SourceID
                    . "\n SourcePersID   => " . $camel->MsgCard->SourcePersID
                    . "\n MsgNotes       => " . $camel->MsgNotes
                    . "\n MsgImages      => " . $camel->MsgImages
                    . "\n MsgADBUnit     => " . $camel->MsgADBUnit
                    . "\n MsgADBUser     => " . $camel->MsgADBUser
                    . "\n MsgDate        => " . $camel->MsgDate
                    . "\n requestId      => " . $request->requestId
                    . "\n\n",
                3,
                "/home/.../!test_scripts/msg_dms.log"
            );
            $message->register_record_id    = isset($camel->MsgCard->SourceID)      ?   $camel->MsgCard->SourceID       :   null;
            $message->citizen_id            = isset($camel->MsgCard->SourcePersID)  ?   $camel->MsgCard->SourcePersID   :   null;
            $message->msgcard               = isset($msgcard)                       ?   $msgcard                        :   null; //, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $message->msgnotes              = isset($camel->MsgNotes)               ?   $camel->MsgNotes                :   null;
            $message->msgimages             = isset($camel->MsgImages)              ?   $camel->MsgImages               :   null;
            $message->msgadbunit            = isset($camel->MsgADBUnit)             ?   $camel->MsgADBUnit              :   null;
            $message->msgadbuser            = isset($camel->MsgADBUser)             ?   $camel->MsgADBUser              :   null;
            $message->msgdate               = isset($camel->MsgDate)                ?   $camel->MsgDate                 :   null;
            $message->requestid             = isset($request->requestId)            ?   $request->requestId             :   null;
            $message->payload = [
                "requestId"         => (isset($request->requestId)       ?   $request->requestId         : ""),
                "client"            => (isset($request->client)          ?   $request->client            : ""),
                "dataSource"        => (isset($request->dataSource)      ?   $request->dataSource        : ""),
                "dataDestination"   => (isset($request->dataDestination) ?   $request->dataDestination   : ""),
                "full"              => $request->input()
            ];
            //    $data = json_decode($rtgk->getRegisterRecord($message->register_record_id, "id"))[0];
            //    $message->district_id   = $data->districtId->id;
            //    $message->district_name = $data->districtId->shortName;
            $message->save();
            error_log(
                "[Inputed] "
                    . date('Y-m-d H:i:s') . " Input.\n" . $message . "\n\n",
                3,
                "/home/.../!test_scripts/msg_dms.log"
            );
            return response(200); //->json($request->all());

        } catch (\Throwable $th) {
            $message->error = "Error.Input data error.\n" . $th;
            $message->payload = [
                "full" => $request->input()
            ];
            $message->save();
            error_log(
                date('Y-m-d H:i:s') . "Error: " . $th->getMessage() . "\n\n",
                3,
                "/home/.../!test_scripts/msg_dms.log"
            );
            return response($th, 500);
        }
        $message->error = "Error.Input data unknown error.\n";
        $message->payload = json_encode([
            "full" => $request->input()
        ]);
        $message->save();

        error_log(
            date('Y-m-d H:i:s') . "Error.Input data unknown error.\n\n",
            3,
            "/home/.../!test_scripts/msg_dms.log"
        );

        return response("Error.Service not avaible.", 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        require_once '/home/.../dms/InfMsgReceiver/api-rtgk.class.php';
        $result = DmsMessage::select([
                "id",
                "uuid",
                "register_record_id",
                "processed",
                "error",
                "msgcard",
                "msgnotes",
                "msgimages",
                "msgadbunit",
                "msgadbuser",
                "msgdate",
                "response_status",
                "response_note",
                "response_id",
                "created",
                "updated",
                "requestid",
                "payload",
                "status",
                "note",
                //"send_data",
                //"send_sign",
                "citizen_id",
                "organization_id",
                "user_id",
                "district_id",
                "district_name"
            ])->find($id);

        try {
            $camel = new \RtgkApi(
                env('RTGK_BASIC'),
                env('RTGK_XSIGNATURE'),
                env('RTGK_URL')
            );
        } catch (\Throwable $th) {
            return $th;
        }
        $citizenId = $camel->registerToCitizen($result->register_record_id);
        $result->citizen_full_txt_info = $camel->getFullTxtInfo($result->register_record_id, "id");
        $result->edit_url = "https://rtgk.kyivcity.gov.ua/citizens/{$citizenId}/edit";
        $result->view_url = "https://rtgk.kyivcity.gov.ua/citizens/{$citizenId}/view";
        return response($result);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // $dms = Dms::where('s_id', $id)->get();
        // return view('todo.edittodo', ['dms' => $dms]);
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

    /**
     * Send the status message to DMS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function send(Request $request, $id)
    {


        require_once '/home/.../dms/InfMsgReceiver/api-rtgk.class.php';
        require_once '/home/.../fleita/HSM.class.php';
        //require_once '/home/.../!test_scripts/trembita.class.php'; //Only for prod!!!
        require_once '/home/.../dms/InfMsgReceiver/sendDms.php'; //Only for prod!!!
        //require_once '/home/.../dms/test-InfMsgReceiver/test-receiver.php';

        if (!$dms = DmsMessage::find($id)) {
            return response()->json(["Error" => "ID [" . $id . "] not found in DB"], 400);
        }

        if ($request->status == "new") {
            $dms->note = $request->note;
            $dms->save();
            return response()->json(['status' => 'Note saved.'], 200);
        }
        $dms->status = $request->status;
        // $dms->is_mother_address = $request->is_mother_address;
        $dms->user_id = $request->user_id;
        $dms->organization_id = $request->organization_id;
        //$dms->version = $request->version;

        // $this->validate($request, [
        //     'is_mother_address' => 'filled',
        //     'status' => 'filled',
        //     'note' => 'filled',
        //     'user_id' => 'filled',
        //     'organization_id' => 'filled',
        //     'version' => 'filled',
        // ]);

        try {
            $camel = new \RtgkApi(
                env('RTGK_BASIC'),
                env('RTGK_XSIGNATURE'),
                env('RTGK_URL')
            );
        } catch (\Throwable $th) {
            return $th;
        }
        //$citizenId = isset($dms->msgcard->SourcePersID)?$dms->msgcard->SourcePersID:null;
        $citizenId = (Register::find($dms->register_record_id))->citizen_id;
        if (!$citizenId) {
            return response("Error: SourcePersID is not set", 404);
        }
        $fio = json_decode($camel->getFIO($dms->msgcard->SourcePersID), false);
        if (!$fio) {
            return response("Error: SourcePersID not find in RTGK", 404);
        }

        $result = new Result;
        $result->requestID = (string) Str::uuid();
        $result->sourceRequestID = trim($dms->requestid);

        if ($dms->status == "successfully") {
            $textFullAdress = $camel->getRegistration($citizenId);
            $textFullAdress = ($textFullAdress == "Відсутня адреса реєстрації мешканця.") ? $request->note : $textFullAdress;
            if (!$textFullAdress) {
                return response("Відсутній опис згоди. У полі \"примітки\".", 404);
            }
            $exit = ["status" => $dms->status, "text" => $textFullAdress];
            $result->requestData = base64_encode(json_encode($exit));
        } elseif ($dms->status == "denied") {
            $exit = ["status" => $dms->status, "text" => "Відмова: " . $request->note];
        }

        $result->requestData =
            //json_encode($exit, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            base64_encode(json_encode($exit));
        $hsm = new \HSM(base64_encode($result->requestData), env('HSM_BARER'), env('HSM_STORE'), env('HSM_STORE_SECRET'));

        if (empty($hsm)) {
            return response("DS not set.", 500);
        }

        $result->requestDataSign = $hsm->DSHash;

        //return [$result->requestID, $result->sourceRequestID, $result->requestData, $result->requestDataSign];

        //$trembita = new \Trembita("prod","InfMsgResult");
        //var_dump($trembita->send((string) $result->requestID, $result->sourceRequestID, $result->requestData, $result->requestDataSign));
        $resp = callPersonInfoService($result->requestID, $result->sourceRequestID, $result->requestData, $result->requestDataSign);
        //print_r("DEBUG  -  status =>".$resp['status'] . " \n text =>".$resp['statusText']."\n");
        try {
            $resp['status'] = isset($resp['status']) ? $resp['status'] : "-empty-";
            $resp['statusText'] = isset($resp['statusText']) ? $resp['statusText'] : "-empty-";
            if (isset($resp['status'])) {
                if ($resp['status'] == 10) {

                    print_r($resp['status'] . " => ALL OK\n text =>" . $resp['statusText'] . "\n");

                    $dms->processed = true;
                    $dms->note =  $request->note;
                    $dms->response_id =  $result->requestID;
                    $dms->response_status = $resp['status'];
                    $dms->response_note = $resp['statusText'];
                    $dms->send_data = $result->requestData;
                    $dms->send_sign = $result->requestDataSign;
                    $dms->save();
                } elseif ($resp['status'] == 20) {

                    print_r($resp['status'] . " => Error;\n Error text =>" . $resp['statusText'] . " \n");

                    $errorText = [
                        "note" => $request->note,
                        "request_id" => $result->requestID,
                        "request_text" => $exit,
                        "resp" => $resp,
                    ];
                    $dms->error = json_encode($errorText, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $dms->processed = false;
                    $dms->note = $textFullAdress;
                    $dms->response_status = $resp['status'];
                    $dms->response_note = $resp['statusText'];
                    $dms->send_data = $result->requestData;
                    $dms->send_sign = $result->requestDataSign;
                    $dms->save();
                } else {

                    $errorText = [
                        "request_id" => $result->requestID,
                        "request_text" => $exit,
                        "resp" => $resp
                    ];
                    $dms->error = json_encode($errorText, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $dms->processed = false;
                    $dms->note = $textFullAdress;
                    $dms->response_status = $resp['status'];
                    $dms->response_note = $resp['statusText'];
                    $dms->send_data = $result->requestData;
                    $dms->send_sign = $result->requestDataSign;
                    $dms->save();
                    // print_r("DEBUG:\nstatus =>".$resp['status'] . " \n text =>".$resp['statusText']."\n");
                    // print_r($resp);
                    return response("Відсутня відповідь від ДМС про прийняття нанних.", 504);
                }
            } else {
                $errorText = [
                    "request_id" => $result->requestID,
                    "request_text" => $exit,
                    "resp" => $resp
                ];
                $dms->error = json_encode($errorText, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $dms->processed = false;
                $dms->note = $textFullAdress;
                $dms->response_status = $resp['status'];
                $dms->response_note = $resp['statusText'];
                $dms->send_data = $result->requestData;
                $dms->send_sign = $result->requestDataSign;
                $dms->save();
                return response("Відсутня відповідь від ДМС про прийняття нанних.", 504);
            }
        } catch (\Throwable $th) {
            $errorText = [
                "error" => $th,
                "note" => $request->note,
                "response_id" => $result->requestID,
                "response_text" => $exit,
                "send_data" => $result->requestData,
                "send_sign" => $result->requestDataSign,
                "resp" => $resp,
            ];
            $dms->error = json_encode($errorText, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $dms->save();
            return response($th, 500);
        }
        return response()->json([$exit, $resp]);
    }

    public function fix()
    {
        require_once '/home/.../dms/InfMsgReceiver/api-rtgk.class.php';

        try {
            //code...
            $result = DmsMessage::orderBy('id', 'asc')->where("requestid", "=", null)->get();
        } catch (\Throwable $th) {
            return response($th, 500);
        }
        foreach ($result as $key => $value) {
            if (isset($value->payload)) {
                $camel = DmsMessage::where("requestid", $value->payload->requestId)->first();
                // dd($camel->uuid);
                if (!$camel) {
                    $message = DmsMessage::find($value->id);
                    $message->register_record_id    = isset($value->payload->full->data->MsgCard->SourceID)         ?   $value->payload->full->data->MsgCard->SourceID          :   null;
                    $message->requestid             = isset($value->payload->full->requestId)                       ?   $value->payload->full->requestId                        :   null;
                    $message->citizen_id            = isset($value->payload->full->data->MsgCard->SourcePersID)     ?   $value->payload->full->data->MsgCard->SourcePersID      :   null;
                    $message->msgcard               = isset($value->payload->full->data->MsgCard)                   ?   $value->payload->full->data->MsgCard                    :   null;
                    $message->msgnotes              = isset($value->payload->full->data->MsgNotes)                  ?   $value->payload->full->data->MsgNotes                   :   null;
                    // dd($value->payload->full->data->MsgNotes);
                    $message->msgimages             = isset($value->payload->full->data->MsgImages)                 ?   $value->payload->full->data->MsgImages                  :   null;
                    $message->msgadbunit            = isset($value->payload->full->data->MsgADBUnit)                ?   $value->payload->full->data->MsgADBUnit                 :   null;
                    $message->msgadbuser            = isset($value->payload->full->data->MsgADBUser)                ?   $value->payload->full->data->MsgADBUser                 :   null;
                    $message->msgdate               = isset($value->payload->full->data->MsgDate)                   ?   $value->payload->full->data->MsgDate                    :   null;
                    $message->citizen_id = (Register::find($message->register_record_id))->citizen_id;
                    require_once '/home/.../dms/InfMsgReceiver/api-rtgk.class.php';

                    try {
                        $rtgk = new \RtgkApi(
                            env('RTGK_BASIC'),
                            env('RTGK_XSIGNATURE'),
                            env('RTGK_URL')
                        );
                    } catch (\Throwable $th) {
                        return $th;
                    }
                    $data = json_decode($rtgk->getRegisterRecord($message->register_record_id, "id"))[0];
                    $message->district_id   = $data->districtId->id;
                    $message->district_name = $data->districtId->shortName;
                    $message->save();

                    //return $message;
                    // $message->requestid             = isset($request->requestId)            ?   $request->requestId             :   null ;

                    print_r($value->payload->requestId);
                    echo "\n";
                } else {
                    // print_r($value->payload->requestId);
                    // echo "\n";
                    // return $camel->requestid;
                    // dd($camel);
                }
            }
        }


        return "Ok";



        return response()->json([$exit, $resp]);
    }
}
