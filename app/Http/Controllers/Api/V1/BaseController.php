<?php

namespace App\Http\Controllers\Api\V1;

use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Dingo\Api\Exception\ValidationHttpException;
use APP\Exceptions\ApiErrDesc;

class BaseController extends Controller{
    // 接口帮助调用
    use Helpers;

    // 返回错误的请求
    protected function errorBadRequest($validator)
    {
        // github like error messages
        // if you don't like this you can use code bellow
        //
        //throw new ValidationHttpException($validator->errors());

        $result = [];
        $messages = $validator->errors()->toArray();
        if ($messages) {
            foreach ($messages as $field => $errors) {
                // var_dump($errors);exit;
                foreach ($errors as $error) {
                    $result[] = [
                        'field' => $field,
                        'code' => $error,
                    ];
                }
            }
        }
        // var_dump($result);exit;
        $msg = !empty($result[0]['code']) ? $result[0]['code']: '';
        //throw new ValidationHttpException($result);
        return  $this->VdtError($msg);
    }

    // 请求成功时对数据进行格式处理
    public function success($data=[],$msg ='') {
        return response()->json([
            'code' => self::SUCCESS,
            'msg' => !empty($msg) ? $msg :__('base.success'),
            'time' => time(),
            'data' => $data
        ]);
    }

    // 响应失败时返回自定义错误信息
    public function responseError($msg = '',$data=[]) {
        return response()->json([
            'code' => self::ERROR_SPECIAL,
            'msg' => !empty($msg) ? $msg :__('base.error'),
            'time' => time(),
            'data' => $data,
        ]);
    }

    // 响应校验失败时返回自定义的信息
    public function vdtError($msg = '',$data=[]) {
        return response()->json([
            'code' => self::VAlID,
            'msgs' => !empty($msg) ? $msg :__('base.vdt'),
            'time' => time(),
            'data' => $data,
        ]);
    }

    // 错误提示方法
    public function error($msg = '',$data=[]){
        return response()->json([
            'code' => self::ERROR,
            'msgs' => !empty($msg) ? $msg :__('base.error'),
            'time' => time(),
            'data' => $data,
        ]);
    }

    /**
     *基础取全部值方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseAll(Request $request){
        $where_data= [];
        $msg = !empty($this->BaseAllVatMsg) ? $this->BaseAllVatMsg : [];
        $validator = Validator::make($request->all(), $this->BaseAllVat,$msg);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        //修改参数  request要存在或者BUWhere存在
        foreach($this->BAWhere as $B_k => $B_v){
            if(!empty($B_v) && empty($B_v[1]) && isset($data[$B_k])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && !empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $RData = (new $this->BaseModels)->BaseAll($where_data,$this->BA,$this->BAOrder);
        if(method_exists($this,'_allFrom')) $RData = $this->_allFrom($RData);
        return  $this->success($RData,__('base.success'));
    }

    /**
     *基础取多个值带分页方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseLimit(Request $request){
        $where_data= [];
        $msg = !empty($this->BaseLVatMsg) ? $this->BaseLVatMsg : [];
        $validator = Validator::make($request->all(), $this->BaseLVat,$msg);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        $cur_page= !empty($data['cur_page'])? $data['cur_page']: 1;
        $size= !empty($data['size'])? $data['size']: 10;
        //修改参数  request要存在或者BUWhere存在
        foreach($this->BLWhere as $B_k => $B_v){
            // var_dump($B_k[1]);exit;
            if(!empty($B_v) && empty($B_v[1]) && isset($data[$B_k])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && !empty($B_v[1]) && $B_v[1] !=''){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $RData = (new $this->BaseModels)->BaseLimit($where_data,$this->BL,$this->BLOrder,$cur_page,$size);
        if(method_exists($this,'_limitFrom')) $RData = $this->_limitFrom($RData);
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
        $msg = !empty($this->BaseOneVatMsg) ? $this->BaseOneVatMsg : ['id.required' =>__('base.vdt')];
        $validator = Validator::make($request->all(), $this->BaseOneVat,$msg);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        //修改参数  request要存在或者BUWhere存在
        // var_dump($this->BOWhere);exit;
        foreach($this->BOWhere as $B_k => $B_v){
            if(!empty($data[$B_k]) && empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && isset($B_v[1]) && $B_v[1] !=''){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $RData = (new $this->BaseModels)->BaseOne($where_data,$this->BO,$this->BOOrder);
        if(method_exists($this,'_oneFrom')) $RData = $this->_oneFrom($RData);
        return  $this->success($RData,__('base.success'));
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
        $msg = !empty($this->BaseCreateVatMsg) ? $this->BaseCreateVatMsg : [];
        $validator = Validator::make($request->all(), $this->BaseCreateVat,$msg);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        //根据配置传入参数
        foreach($this->BaseCreate as $k => $v){
            if(isset($data[$k]) && $data[$k] !=''){
                $create_data[$k] = $data[$k];
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'time'){
                $create_data[$k] = time();
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'date'){
                $create_data[$k] = date('Y-m-d H:i:s');
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
        $msg = !empty($this->BaseUpdateVatMsg) ? $this->BaseUpdateVatMsg : [];
        $validator = Validator::make($request->all(), $this->BaseUpdateVat,$msg);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        //根据配置传入参数
        foreach($this->BaseUpdate as $k => $v){
            if(isset($data[$k]) && $data[$k] !=''){
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
        }
        //修改参数  request要存在或者BUWhere存在
        foreach($this->BUWhere as $B_k => $B_v){
            if(isset($data[$B_k]) && empty($B_v[1])){
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
     *基础删除方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required',
        ],['ids.required'=>__('base.vdt')]);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $ids = $request->ids;
        if(empty($request->name))
            $data = (new $this->BaseModels)->BaseDelete($ids);
        else
            $data = (new $this->BaseModels)->BaseDelete($ids);

        if($data)
            return $this->success();
        else
            return  $this->error();
    }
}