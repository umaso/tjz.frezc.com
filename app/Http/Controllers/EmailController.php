<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\User;
use Validator;
use Mail;
use DB;
use Hash;
use JWTAuth;

class EmailController extends Controller
{
  public function __construct()
  {
      $this->middleware('jwt.auth', ['only' => ['bindEmail']]);
      $this->middleware('email', ['only' => ['verifyEmail', 'bindEmail']]);
  }

  public function sendVerifyEmail(Request $request){
    $v = Validator::make($request->all(), [
        'email' => 'required|email'
    ]);

    if ($v->fails()){
        return $this->response->error($v->errors(), 400);
    }

    $email = $request->input('email');

    $verify = DB::table('email_verifications')->where('email', $email)->get();
    if(count($verify) > 0){
      $this->clearVerification($email);
    }

    $token = $this->generateToken($email);
    $send_time = time();
    $send_at = strftime('%Y-%m-%d %X', $send_time);

    $avalible_before = strftime('%Y-%m-%d %X', $send_time + 3600);

    Mail::send('emails.verification',
      ['token' => $token, 'avalible_before' => $avalible_before],
      function ($message) use($email) {
        $message->to($email, 'dear')->subject('淘兼职邮箱验证');
    });

    DB::insert('insert into email_verifications (email, token, send_at) values (?,?,?)',
                [$email, $token, $send_at]);

    return 'success';
  }

  public function verifyEmail(Request $request){
    $user = User::where('email', $request->input('email'))->first();
    $user->email_verified = 1;
    $user->save();
    return 'success';
  }

  public function bindEmail(Request $request){
    $user = JWTAuth::parseToken()->authenticate();
    if ($user->email != null) {
      return $this->response->error('email has binded', 400);
    } else {
      $user->email = $request->input('email');
      $user->email_verified = 1;
      $user->save();
      return 'success';
    }
  }

  private function generateToken($email){
    // return Hash::make($email.date('Ymd').str_random(16));
    return str_random(6);
  }

  private function clearVerification($email){
    DB::delete('delete from email_verifications where email = ?', [$email]);
  }

  /**
  *  测试用方法
  */
  public function emailSend(Request $request){
    $email = $request->input('email');
    Mail::send('emails.verification', ['token' => 'ftTf43', 'avalible_before' => '2014-5-13 13:00:22'], function ($message) use($email) {
      $message->to($email, 'dear')->subject('淘兼职邮箱验证');
    });
  }
}
