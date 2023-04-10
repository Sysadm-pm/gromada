<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });

// $router->get('user/{id}/', function ($id) {
//     return 'User ' . $id;
// });
// $router->get('p/', [
//     'as' => 'profile', 'uses' => 'rtgk_back@nain',
//     'name'=> 'main'
// ]);

$router->group(['prefix' => 'api/'], function ($app) {
    $app->get('login/', 'UsersController@authenticate');
    //$app->post('dms/', 'RtgkController@store');
    /**
     * @OA\Get(
     *     path="/api/dms",
     *     summary="Get all DMS messages",
     *     @OA\Response(
     *         response="200",
     *         description="List of DMS messages"
     *     )
     * )
     */
    $app->get('dms/', 'RtgkController@index');
    $app->get('dms/{id}/', 'RtgkController@show');
    $app->put('dms/{id}/', 'RtgkController@update');
    $app->post('dms/send/{id}/', 'RtgkController@infMsgResult');

    $app->post('dms/test/{id}/', 'RtgkController@infMsgTest');
    $app->post('dms/test_fake/', 'RtgkController@infFake');

    $app->get('Fsdms/', 'RtgkController@Findex');
    $app->get('Fdms/', 'RtgkController@Findex'); //mirror method for testing Update in RTGK
    $app->get('Fdms/{id}/', 'RtgkController@Fshow');
    $app->put('Fdms/{id}/', 'RtgkController@Fupdate');

    $app->get('notifications/', 'NotificationController@index');
    $app->get('notifications/search', 'NotificationController@uuidSearch');

    $app->get('message/', 'DmsMessageController@index');
    $app->post('message/fix/', 'DmsMessageController@fix');
    $app->get('message/{id}/', 'DmsMessageController@show');
    $app->post('message/{id}/', 'DmsMessageController@send');


});

$router->post('registration-requests/', 'DmsMessageController@store');
