<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    const ERROR = 500;//异常
    const SUCCESS = 200;//正常code
    const TOKEN = 405;//token不存在
    const VAlID = 501;//验证不通过
    const ERROR_SPECIAL = 502;//特殊异常
    public function __construct(Request $request){
        #多语言处理
        if(!empty($request->lang)) App::setLocale($request->lang);
    }

    //跳转主页
    public function toIndex()
    {
        // View::addExtension('html', 'php');
        
        $url = cdnurl('').'/cms/index.html';
        // var_dump($url);exit;
        Header("HTTP/1.1 303 See Other");
        Header("Location:$url");exit;
        // return view()->file(public_path().'/cms/index.html');
    }
}