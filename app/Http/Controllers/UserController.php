<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request)
    {
        //check if user is alredy exist check start
        $rules = [
            'socialEmail' => 'required|email'
        ];
        $customMessages = [
            'required' => 'Please fill email :attribute'
        ];
        $this->validate($request, $rules, $customMessages);
        $email    = $request->input('socialEmail');
        try {
            $login = User::where('SOCIAL_EMAIL', $email)->limit(1)->first();
            if (!empty($login) && $login->count() > 0) { //user already signup return token in response
                return $this->login($login);
            } else {
                //registration start 
                $rules = [
                    'socialName' => 'required|alpha_num|min:5|max:150',
                    'socialEmail' => 'required|email|unique:users,SOCIAL_EMAIL',
                    // 'password' => 'required',
                ];
                $customMessages = [
                    'required' => 'Please fill attribute :attribute'
                ];
                $this->validate($request, $rules, $customMessages);
                try {
                    $hasher = app()->make('hash');
                    $phoneNumber = $request->input('phoneNumber');
                    $socialEmail = $request->input('socialEmail');
                    $socialEmail = $request->input('socialEmail');
                    $deviceId = $request->input('deviceId');
                    $socialType = $request->input('socialType');
                    $socialName = $request->input('socialName');
                    $gender = $request->input('gender');
                    $countryCode = $request->input('countryCode');
                    $userLocale = $request->input('userLocale');
                    $advertisingId = $request->input('advertisingId');
                    $versionName = $request->input('versionName');
                    $versionCode = $request->input('versionCode');
                    // $password = $hasher->make($request->input('password'));
                    $api_token = sha1($socialEmail . time());
                    $userCreate = User::create([
                        'SOCIAL_NAME' => $socialName,
                        'SOCIAL_EMAIL' => $socialEmail,
                        // 'password' => $password,
                        'API_TOKEN' => $api_token
                    ]);
                    if (!empty($userCreate->id)) {
                        $res['status'] = '200';
                        $res['message'] = 'Success';
                        $res['userId'] = $userCreate->id;
                        $res['socialName'] = $userCreate->SOCIAL_NAME;
                        $res['type'] = 'register';
                        $res['api_token'] = $userCreate->API_TOKEN;
                        return response($res, 200);
                    } else {
                        $res['status'] = '201';
                        $res['message'] = 'Failed';
                        return response($res, 201);
                    }
                } catch (\Illuminate\Database\QueryException $ex) {
                    $res['status'] = false;
                    $res['message'] = $ex->getMessage();
                    return response($res, 500);
                }
                //registration end 
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $res['success'] = false;
            $res['message'] = $ex->getMessage();
            return response($res, 500);
        }
        //check if user is alrady exist check end
    }
    private function login($login)
    { //if user already signup then logged in
        try {
            $api_token = sha1($login->socialEmail . time());
            $update_token = User::where('USER_ID', $login->USER_ID)->update(['API_TOKEN' => $api_token]);
            
            if ($update_token) {
                $res['status'] = '200';
                $res['message'] = 'Success';
                $res['userId'] = $login->USER_ID;
                $res['socialName'] = $login->SOCIAL_NAME;
                $res['type'] = 'login';
                $res['api_token'] = $api_token;
                return response($res, 200);
            } else {
                $res['status'] = '201';
                $res['message'] = 'Login_Failed';
                return response($res, 200);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $res['status'] = false;
            $res['message'] = $ex->getMessage();
            return response($res, 500);
        }
    }
    
}