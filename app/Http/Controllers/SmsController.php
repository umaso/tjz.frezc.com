<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Curl\Curl;
use Validator;
use App\User;
use Hash;
use JWTAuth;

class SmsController extends Controller
{
  public function __construct()
  {
      $this->middleware('jwt.auth', ['only' => ['bindPhone']]);
      $this->middleware('sms', ['only' => ['registerByPhone', 'bindPhone', 'resetPassword']]);
  }

  public function getSmsCode(Request $request){
    $v = Validator::make($request->all(), [
        'phone' => 'required|regex:/[0-9]+/'
    ]);

    if ($v->fails())
    {
        return $this->response->error($v->errors(), 400);
    }

    $curl = new Curl();
    $curl->setHeader('Content-Type', 'application/json');
    $curl->setHeader('X-LC-Id', env('SMS_APPID', ''));
    $curl->setHeader('X-LC-Key', env('SMS_APPKEY', ''));

    $body = json_encode([
      'mobilePhoneNumber' => $request->query('phone'),
      'ttl' => 60
    ]);

    $curl->post('https://api.leancloud.cn/1.1/requestSmsCode', $body);

    return response()->json($curl->response);
  }


  public function registerByPhone(Request $request){
    $v = Validator::make($request->all(), [
        'password' => 'required|between:6,32',
        'nickname' => 'required|between:1,16'
    ]);

    if ($v->fails())
    {
      return $this->response->error($v->errors(), 400);
    }

    $user = new User;
    $user->phone = $request->input('phone');
    $user->nickname = $request->input('nickname');
    $user->password = Hash::make($request->input('password'));
    $user->save();

    return 'success';
  }

  public function bindPhone(Request $request){
    $user = JWTAuth::parseToken()->authenticate();
    if ($user->phone != null) {
      return $this->response->error('phone has binded', 400);
    } else {
      $user->phone = $request->input('phone');
      $user->save();
      return 'success';
    }
  }

  public function resetPassword(Request $request) {
    $v = Validator::make($request->all(), [
        'password' => 'required|between:6,32'
    ]);

    if ($v->fails())
    {
      return $this->response->error($v->errors(), 400);
    }

    $user = User::where('phone', $request->input('phone'))->first();
    $user->password = Hash::make($request->input('password'));
    $user->save();

    return 'success';
  }

  public function test(Request $request){
    $curl = new Curl();

    $curl->post('http://tjz.frezc.com/auth', [
      'email' => '504021398@qq.com',
      'password' => 'secret'
    ]);

    return response()->json($curl->response);
  }
}
