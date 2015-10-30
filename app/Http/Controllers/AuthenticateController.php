<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use Storage;

class AuthenticateController extends Controller
{
  public function __construct()
  {
       $this->middleware('jwt.auth', ['except' => ['authenticate']]);
  }

  public function index()
  {
      $users = User::all();
      // return $users;
      // return $this->response->errorNotFound();
      return $user = JWTAuth::parseToken()->authenticate();
  }

  public function getResume(Request $request){
      $user = JWTAuth::parseToken()->authenticate();
      $resumes = $user->resumes();
      if ($resume_id = $request->query('id')){
        return $resumes->where('id', $resume_id)->get()->toArray();
      } else {
        return $resumes->get()->toArray();
      }
  }

  public function delResume(Request $request){
      $user = JWTAuth::parseToken()->authenticate();
      $resumes = $user->resumes();
      if ($resume_id = $request->query('id')){
        $resume = $resumes->where('id', $resume_id)->first();
        if ($resume){
          $resume->delete();
          return 'deleted';
        } else {
          return $this->response->error('resume not found', 404);
        }
      } else {
        return $this->response->errorBadRequest();
      }
  }

  public function updateAvatar(Request $request){
    $user = JWTAuth::parseToken()->authenticate();
    if ($request->hasFile('avatar') && $request->file('avatar')->isValid()){
        Storage::disk('ftp')->put(
            'avatars/'.$user->id,
            file_get_contents($request->file('avatar')->getRealPath())
        );
        $url = 'http://static.frezc.com/static/avatars/'.$user->id;
        $user->avatar = $url;
        $user->save();
        return $url;
    } else {
        return $this->response->errorBadRequest();
    }
  }

  public function addResume(Request $request){
      $user = JWTAuth::parseToken()->authenticate();
      if ($request->hasFile('photo') && $request->file('photo')->isValid()){

      }
  }

  public function authenticate(Request $request)
  {
      // grab credentials from the request
      $credentials = $request->only('email', 'password');

      try {
          // attempt to verify the credentials and create a token for the user
          if (! $token = JWTAuth::attempt($credentials)) {
              return response()->json(['error' => 'invalid_credentials'], 401);
          }
      } catch (JWTException $e) {
          // something went wrong whilst attempting to encode the token
          return response()->json(['error' => 'could_not_create_token'], 500);
      }

      // all good so return the token

      return response()->json([
        'user' => User::where('email', $request->Input('email'))->first(),
        'token' => $token
      ]);
  }
}