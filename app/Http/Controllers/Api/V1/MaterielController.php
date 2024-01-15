<?php
    
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Http\Request;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MaterielController extends BaseController
{
    protected $BaseModels = 'App\Models\Materiel';
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

    protected $BaseCreateVat = [];//新增验证

    protected $BaseCreate =[
        'name'=>'','frigate_name'=>'','commodity_name'=>'',
        'pinfan'=>'','rules'=>'','num'=>'',
        'contact_method'=>'',
        'email'=>'',
        'phone'=>'',
        'contents'=>'',
        'policy'=>'',
        'username'=>'',
        'mater_price'=>'',
        'auditor'=>'',
        // 'examinetime'=>'',
        'iscart'=>'',
        'createtime' => ['type','date'],
    ];//新增数据

    protected $BaseUpdateVat = [
        'id' =>        'required',
    ];//新增验证
    protected $BaseUpdate =[
        'id'=>'', 'name'=>'','frigate_name'=>'','commodity_name'=>'',
        'pinfan'=>'','rules'=>'','num'=>'',
        'contact_method'=>'',
        'email'=>'',
        'phone'=>'',
        'contents'=>'',
        'policy'=>'',
        'username'=>'',
        'mater_price'=>'',
        'auditor'=>'',
        // 'examinetime'=>'',
        'iscart'=>'',
        // 'updatetime' => ['type','time'],
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
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        $cur_page= !empty($data['cur_page'])? $data['cur_page']: 1;
        $size= !empty($data['size'])? $data['size']: 10;
        //修改参数  request要存在或者BUWhere存在
        foreach($this->BLWhere as $B_k => $B_v){
            // var_dump($B_k[1]);exit;
            if(!empty($B_v) && empty($B_v[1]) && !empty($data[$B_k])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && !empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $id = Auth::id();
        $username  = DB::table('member')->where('id','=',$id)->pluck('username');
        $username = !empty($username[0]) ? $username[0]: '';
        $where_data[] = ['username','=',$username ];
        $RData = (new $this->BaseModels)->BaseLimit($where_data,$this->BL,$this->BLOrder,$cur_page,$size);
        return  $this->success($RData,__('base.success'));
    }

}
