<?php
    
namespace App\Http\Controllers\Admin\V1\Auth;

use App\Http\Controllers\Admin\V1\BaseController;
use Illuminate\Http\Request;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;
use App\Models\RolesUser;
use App\Models\RoleMenu;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RolesController extends BaseController
{
    protected $BaseModels = 'App\Models\Roles';
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
        'name' => 'required',//角色名
        'display_name' => 'required',//显示名
        'description' => 'required',//描述
        // 'icon' => 'required',//图标
    ];//新增验证
    protected $BaseCreate =[
        'name'=>'','password'=>'','email'=>'',
        'created_at' => ['type','date'],
    ];//新增数据

    protected $BaseUpdateVat = [
        'id' => 'required',//角色名
        // 'name' => 'required',//角色名
        // 'display_name' => 'required',//显示名
        // 'description' => 'required',//描述
    ];//新增验证
    protected $BaseUpdate =[
        'id'=>'','name'=>'','password'=>'','email'=>'',
        'updated_at' => ['type','date'],
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据

    public function roleList(Request $request)
    {
        $validator = Validator::make($request->all(),['id' => 'required'] );
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $RolesUserModel = (new RolesUser);
        $data = $RolesUserModel->menus($request->id);
        return $this->success($data,__('base.success'));
    }

    public function setRoleList(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required',
            'menu_ids' => 'required',
            ] );
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $RolesUserModel = (new RoleMenu());
        $roleList = $request->menu_ids;
        $roleList_ids = explode(',',$roleList);
        $array = [];
        foreach($roleList_ids as $v){
            $array[] = ['role_id'=>$request->id,'menu_id'=>$v,];
        }

        DB::beginTransaction();
        // 你也可以通过 rollBack 方法来还原事务：
        $RolesUserModel->where('role_id','=',$request->id)->delete();
        $status =  $RolesUserModel->insert($array);
        if($status){
            DB::commit();
            return $this->success();
        }else{
            DB::rollBack();
            return $this->error();
        }
    }
}
