<?php
    
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;
use App\Models\Attachment;
use  App\Helpers\functions;


class AttachmentController extends BaseController
{
    protected $BaseModels = 'App\Models\Attachment';
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
    protected $BaseCreate =[];//新增数据

    protected $BaseUpdateVat = [
        'id' =>        'required',
    ];//新增验证
    protected $BaseUpdate =[];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据

    public function add(Request $request)
    {
        $file = $request->file('file');
        $destinationPath = '/uploads/admin/'.date('Y-m-d'); // public文件夹下面uploads/xxxx-xx-xx 建文件夹
        $extension = $file->getClientOriginalExtension();   // 上传文件后缀

        $fileName = date('YmdHis').mt_rand(100,999).'.'.$extension; // 重命名
        $status = $file->move(public_path().$destinationPath, $fileName); // 保存图片
        if( !$status){ $this->error(); }

        $data['path'] = $destinationPath.'/'.$fileName;
        $data['uid'] = Auth::id();
        $data['mime'] = $file->getClientMimeType();
        $data['ext'] = $file->getClientOriginalExtension();
        // $data['size'] = $file->getClientOriginalExtension();
        $data['createtime'] = time();
        $dataStatus = (new Attachment)->BaseCreate($data);
        $data['id'] =  $dataStatus;
        $data['url_path'] = cdnurl($data['path'],true);
        if( $dataStatus)
            return   $this->success([$data],__('base.success'));
        else
            return   $this->error();
    }

    public function addAll(Request $request)
    {
        $file = $request->file('file');
        $dadaArray = [];
        foreach ($file as $key => $image) {
            $destinationPath = '/uploads/admin/'.date('Y-m-d'); // public文件夹下面uploads/xxxx-xx-xx 建文件夹
            $extension = $image->getClientOriginalExtension();   // 上传文件后缀

            $fileName = date('YmdHis').mt_rand(100,999).'.'.$extension; // 重命名
            $status = $image->move(public_path().$destinationPath, $fileName); // 保存图片
            if( !$status){ $this->error(); }

            $data['path'] = $destinationPath.'/'.$fileName;
            $data['uid'] = Auth::id();
            $data['mime'] = $image->getClientMimeType();
            $data['ext'] = $image->getClientOriginalExtension();
            // $data['size'] = $file->getClientOriginalExtension();
            $data['createtime'] = time();
            $dataStatus = (new Attachment)->BaseCreate($data);
            $data['id'] =  $dataStatus;
            $data['url_path'] = cdnurl($data['path'],true);
            $dadaArray[] = $data;
        }
        return   $this->success($dadaArray,__('上传成功'));
    }
}
