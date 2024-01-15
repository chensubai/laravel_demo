<?php
    
namespace App\Http\Controllers\Admin\V1\Auth;

use App\Http\Controllers\Admin\V1\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;
use App\Models\Roles;
use App\Models\AdminUsers;

class UserController extends BaseController
{
    protected $BaseModels = 'App\Models\AdminUsers';
    protected $BaseAllVat = [];//获取全部验证
    protected $BAWhere  = [];//获取全部Where条件
    protected $BA  = ['*'];//获取全部选取字段 *是全部
    protected $BAOrder  = [['id','desc']];//获取全部字段排序

    protected $BaseLVat = [];//获取分页全部验证
    protected $BLWhere  = [];//获取全部分页Where条件
    protected $BL  = ['*'];//获取全部分页选取字段 *是全部
    protected $BLOrder  = [['id','desc']];//获取全部分页字段排序

    protected $BaseOneVat = ['id' => 'required',];//单个处理验证
    protected $BOWhere = ['id'=>['=','']];//单个查询验证
    protected $BO = ['*'];//单个选取字段；*是全部
    protected $BOOrder = [['id','desc']];//单个选取字段排序

    protected $BaseCreateVat = [
        'username' => 'required',//名称
        'password' => 'required',//密码
        'email' => 'required',//邮箱
        'avatar' => 'required',//头像
        'roles_id' => 'required',//角色
    ];//新增验证

    protected $BaseCreate =[
        'username'=>'','password'=> ['type','password'],'email'=>'',
        'sex'=>'','avatar'=>'','roles_id'=>'',
        'created_at' => ['type','date'],
    ];//新增数据

    protected $BaseUpdateVat = ['id' => 'required',];//新增验证
    protected $BaseUpdate =[
        'id'=>'','username'=>'','email'=>'',
        'password'=> ['type','password'],
        'sex'=>'','avatar'=>'','roles_id'=>'',
        'updated_at' => ['type','date'],
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据

    /**
     *基础取多个值带分页方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseLimit(Request $request){
        $where_data= [];
        $validator = Validator::make($request->all(), $this->BaseLVat);
        if ($validator->fails()) return $this->errorBadRequest($validator->messages);
        $data = $request->all();
        $cur_page= !empty($data['cur_page'])? $data['cur_page']: 1;
        $size= !empty($data['size'])? $data['size']: 10;
        //修改参数  request要存在或者BUWhere存在
        foreach($this->BLWhere as $B_k => $B_v){
            if(!empty($B_v) && empty($B_v[1]) && !empty($data[$B_k])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && !empty($B_k[1])){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $RData = (new $this->BaseModels)->BaseLimit($where_data,$this->BL,$this->BLOrder,$cur_page,$size);
        $rolesModel = (new Roles);
        if(!empty($RData['data'])){
            foreach($RData['data'] as $k => &$v){
                $name = '';
                if(!empty($v['roles_id'])){
                  $name = $rolesModel::where('id','=',$v['roles_id'])->value('display_name');
                }
                $v['display_name'] = $name;
            }
        }
        return  $this->success($RData,__('base.success'));
    }

    /**
     *基础取单个值方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseOne(Request $request)
    {
        $where_data= [];
        $validator = Validator::make($request->all(), $this->BaseOneVat);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        //修改参数  request要存在或者BUWhere存在
        // var_dump($this->BOWhere);exit;
        foreach($this->BOWhere as $B_k => $B_v){
            if(!empty($data[$B_k]) && empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && !empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $rolesModel = (new Roles);
        $RData = (new $this->BaseModels)->BaseOne($where_data,$this->BO,$this->BOOrder);
        if(!empty($RData) && $RData !='' && $RData !=NULL){
            $name = '';

            if(!empty($RData->roles_id)){
              $name = $rolesModel::where('id','=',$RData->roles_id)->value('display_name');
            }
            $RData->display_name = $name;
        }
        return  $this->success($RData,__('base.success'));
    }

      /**
     *基础修改方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseUpdate(Request $request)
    {
        $where_data= [];
        $update_data = [];
        $validator = Validator::make($request->all(), $this->BaseUpdateVat);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        if(!empty( $data['username'])){
            $userStatus = AdminUsers::where('username','=',$data['username'])->
            where('id','!=',$data['id'])->first();
            if(!empty($userStatus) && $userStatus !=NULL) return  $this->error(__('base.userCreate'));
        }

        if(!empty( $data['email'])){
            $userStatus = AdminUsers::where('email','=',$data['email'])->
            where('id','!=',$data['id'])->first();
            if(!empty($userStatus) && $userStatus !=NULL) return  $this->error(__('base.userCreateEmail'));
        }

        //根据配置传入参数
        foreach($this->BaseUpdate as $k => $v){
            if(!empty($data[$k])){
                $update_data[$k] = $data[$k];
            }
            if(!empty($v) && empty($v[1])){
                $update_data[$k] = $v;
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'time'){
                $update_data[$k] = time();
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'date'){
                $update_data[$k] = date('Y-m-d H:i:s');
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'password'){
                $update_data[$k] =  Hash::make($update_data[$k]);
            }
        }
        //修改参数  request要存在或者BUWhere存在
        foreach($this->BUWhere as $B_k => $B_v){
            if(!empty($data[$B_k]) && empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && !empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $RData = (new $this->BaseModels)->BaseUpdate($where_data,$update_data);
        if($RData)
            return  $this->success($data,__('base.success'));
        else
            return  $this->error();
    }

    /**
     *基础新增方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseCreate(Request $request)
    {
        $create_data = [];
        $validator = Validator::make($request->all(), $this->BaseCreateVat);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        $userStatus = AdminUsers::where('username','=',$data['username'])->first();
        if(!empty($userStatus) && $userStatus !=NULL) return  $this->error(__('base.userCreate'));
        $userStatus = AdminUsers::where('email','=',$data['email'])->first();
        if(!empty($userStatus) && $userStatus !=NULL) return  $this->error(__('base.userCreateEmail'));
        //根据配置传入参数
        foreach($this->BaseCreate as $k => $v){
            if(!empty($data[$k])){
                $create_data[$k] = $data[$k];
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'time'){
                $create_data[$k] = time();
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'date'){
                $create_data[$k] = date('Y-m-d H:i:s');
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'password'){
                $create_data[$k] =  Hash::make($create_data[$k]);
            }
        }
        //修改参数  request要存在或者BUWhere存在
        $id = (new $this->BaseModels)->BaseCreate($create_data);
        if($id){
            $data['id'] = $id;
            return  $this->success($data,__('base.success'));
        }else{
            return  $this->error();
        }
    }
}
