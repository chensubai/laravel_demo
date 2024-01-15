<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Authorization;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;
use App\Models\User;


class AuthController extends BaseController
{

    public function codeImg(){
        return $this->success(['code' => app('captcha')->create('default', true)]);
    }
    // 注册接口
    public function register(Request $request) {
        // $isPhone = preg_match("/^1[3456789]\d{9}$/", $request->phone);
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }
        $user = User::where('username', $request->username)->first();
        if(!empty($user) || $user != NULL) return $this->error(__('auth.userNull'));
        $user = $this->create($request->all());
        if ($user->save() == 1) {
            $token = Auth::fromUser($user);
            if($token !="" || $token !=null) {
                $authorization = new Authorization($token);
                $user_data = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'avatar' => $user->avatar,
                    'status' => 0,
                ];
                $update = [
                        'last_login_time' => time(),
                        'last_login_ip' => get_ip(),
                        'login' => $user->login+1,
                ];
                User::where('username', $request->username)->update($update);
                return $this->success(['token' => $authorization->toArray(), 'user' => $user_data ]);
            }
            User::find($user->id)->delete();
            return $this->error();
        } else {
            return $this->error();
        }
    }

    protected function validator(array $data) {
        if(empty($data['key'])){
            return Validator::make($data, [
                'key' => 'required'
                ],
                [
                    'key.required' => __('api.auth.register.key.required'),
                ]
            );
        }

        return Validator::make($data, [
                'username' => 'required|unique:member',
                'nickname' => 'required',
                'mobile' => 'required|unique:member',
                'email' => 'required|email|max:255|unique:member',
                'password' => 'required|min:6',
                'captcha' => 'required|captcha_api:' . $data['key']
            ],
            [
                'username.required' => __('api.auth.register.username.required'),
                'username.unique' => __('api.auth.register.username.unique'),
                'nickname.required' => __('api.auth.register.nickname.required'),
                'mobile.required' => __('api.auth.register.mobile'),
                'mobile.unique' => __('api.auth.register.mobile.unique'),
                'email.required' => __('api.auth.register.email'),
                'email.email' => __('api.auth.register.email.email'),
                'email.max' => __('api.auth.register.email.email'),
                'email.unique' => __('api.auth.register.email.unique'),
                'password.required' => __('api.auth.register.password.required'),
                'captcha.required' => __('api.auth.register.captcha.required'),
                'captcha.captcha_api' => __('api.auth.register.captcha.captcha_api'),
            ]
        );
    }
    protected function create(array $data) {
        $passwordAarray = encrypt_password($data['password']);
        $password = $passwordAarray['password'];
        $encrypt =  $passwordAarray['encrypt'];
        return User::create([
            'username' => $data['username'],
            'nickname' => $data['nickname'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
            'password' =>  $password,
            'encrypt' =>  $encrypt,
        ]);
    }

    // 登录接口
    public function login(Request $request)
    {
        if(empty($request->key)){
            $validator = Validator::make($request->all(), [
                'key' => 'required'
                ],
                [
                    'key' => __('api.auth.login.key.required'),
                ]
            );
            if ($validator->fails()) {
                return $this->errorBadRequest($validator);
            }
        }

        $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
                // 'code' =>   'required',
                'captcha' => 'required|captcha_api:' . $request->key
            ],
            [
                'username.required' => __('api.auth.login.username.required'),
                'password.required' => __('api.auth.login.password.required'),
                'captcha.required' => __('api.auth.login.captcha.required'),
                'captcha.captcha_api' => __('api.auth.login.captcha.captcha_api'),
            ]
        );
        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }
        $user = User::where('username', $request->username)->first();
        if(empty($user) || $user == NULL) return $this->error(trans('auth.userNull'));

        $password = encrypt_password($request->password, $user->encrypt);
        if ($password !=  $user->password) return $this->error(trans('auth.failed'));
        $token = Auth::fromUser($user);
        if($token !="" || $token !=null) {
            $authorization = new Authorization($token);
            $user_data = [
                'id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'avatar' => $user->avatar,
                'status' => $user->status,
            ];
            $update = [
                    'last_login_time' => time(),
                    'last_login_ip' => get_ip(),
                    'login' => $user->login+1,
            ];
            User::where('username', $request->username)->update($update);
            return $this->success(['token' => $authorization->toArray(), 'user' => $user_data ]);
        }
        return $this->error(trans('auth.incorrect'));
        // __('sent');
    }

    //刷新token接口 (一个 token 只能刷新一次 ,并且需要在 token 的过期时间内进行刷新） 
    public function update()
    {
        $authorization = new Authorization(Auth::refresh());
        return $this->responseSuccess(['token' => $authorization->toArray(), 'user' => $authorization->user()]);
    }

    // 注销接口
    public function destroy()
    {
        Auth::logout();
        return $this->success([],__('auth.destroy'));
    }
}
