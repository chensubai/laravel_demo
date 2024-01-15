<?php
    
namespace App\Http\Controllers\Admin\V1\Auth;

use App\Http\Controllers\Admin\V1\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\AdminAuthorization;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Dingo\Api\Exception\StoreResourceFailedException;
use App\Models\AdminUser;
use App\Models\AdminUsers;
use App\Models\RolesUser;
use App\Models\Roles;
// use Auth;

class AuthController extends BaseController
{
    protected $BaseModels = 'App\Models\Test';
    protected $BaseAllVat = [];//获取全部验证
    protected $BAWhere  = [];//获取全部Where条件
    protected $BA  = ['*'];//获取全部选取字段 *是全部
    protected $BAOrder  = [['id','desc']];//获取全部字段排序

    protected $BaseLVat = [];//获取分页全部验证
    protected $BLWhere  = [];//获取全部分页Where条件
    protected $BL  = ['*'];//获取全部分页选取字段 *是全部
    protected $BLOrder  = [['id','desc']];//获取全部分页字段排序

    protected $BaseOneVat = [];//单个处理验证
    protected $BOWhere = ['id'=>['=','']];//单个查询验证
    protected $BO = ['*'];//单个选取字段；*是全部
    protected $BOOrder = [['id','desc']];//单个选取字段排序

    protected $BaseCreateVat = [];//新增验证
    protected $BaseCreate =[
        'name'=>'','password'=>'','email'=>'',
        'created_at' => ['type','date'],
    ];//新增数据

    protected $BaseUpdateVat = [];//新增验证
    protected $BaseUpdate =[
        'id'=>'','name'=>'','password'=>'','email'=>'',
        'created_at' => ['type','date'],
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据


    protected function validator(array $data) {
        return Validator::make($data, [
            'username' => 'required',
            // 'phone' => 'required|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6',
        ]);
    }

    // 登录接口
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'username' => 'required',
                'password' => 'required|min:6|max:25',
            ],
            [
                'username.required' => __('admin.auth.login.username.required'),
                'password.required' =>__('admin.auth.login.password.required'),
                'password.min' =>__('admin.auth.login.password.min'),
                'password.max' =>__('admin.auth.login.password.max')
            ]
        );
        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }
        $user = AdminUser::where('username', $request->username)->first();
        if ($user && Hash::check($request->get('password'), $user->password)){

            $token = Auth::fromUser($user);

            if($token !="" || $token !=null) {
                $authorization = new AdminAuthorization($token);
                return $this->success(['token' => $authorization->toArray(), 'user' => $user]);
            }
            return $this->error(trans('auth.incorrect'));
        }
        return $this->responseError(__('auth.failed'));
    }

    //刷新token接口 (一个 token 只能刷新一次 ,并且需要在 token 的过期时间内进行刷新） 
    public function update()
    {
        $authorization = new AdminAuthorization(Auth::refresh());
        return $this->success(['token' => $authorization->toArray(), 'user' => $authorization->user()]);
    }

    //权限列表
    public function getMenus()
    {
        $AdminUsersModel = (new AdminUsers);
        $RolesUserModel = (new RolesUser);
        $id = Auth::id();
        $info = $AdminUsersModel->BaseOne([['id','=',$id]],['*'],[]);
        if(!empty($info) && $info != NULL){
           $data = $RolesUserModel->menus();
        }else{
            $data = $RolesUserModel->menus($info['roles_id']);
        }
        return $this->success($data,__('base.success'));
    }

    // 注销接口
    public function destroy()
    {
        Auth::logout();
        return $this->success([],__('auth.destroy'));
    }
}
