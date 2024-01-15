<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//接管管理路由
$api = app('Dingo\Api\Routing\Router');

$api->version('v1',[
    'namespace' => 'App\Http\Controllers',
    'cors'
], function ($api) {
    $api->get('/', 'Controller@toIndex');
    $api->get('/cms/index', 'Controller@toIndex2');
    //规划前台接口
    $api->group(['namespace' => 'Api\V1', "prefix" => 'api'], function ($api) {
        //注册
        $api->post('/register', 'Auth\AuthController@register');
        //验证码
        $api->get('/codeImg', 'Auth\AuthController@codeImg');
        //登录
        $api->post('/login', 'Auth\AuthController@login');
        //轮播
        $api->get('/slide', 'SlideController@BaseAll');
        //店铺
        $api->get('/shop', 'ShopController@BaseAll');
        //网站信息
        $api->get('/config', 'ConfigController@BaseAll');
        //品牌
        $api->get('/brand', 'ProductController@brandLimit');
        //系列
        $api->get('/series', 'ProductController@seriesLimit');
        //商品
        $api->get('/product', 'ProductController@BaseLimit');
        //商品详情
        $api->get('/product/info', 'ProductController@BaseOne');
        //文章
        $api->get('/article', 'ArticleController@BaseAll');
        //文章
        $api->get('/seriesAll', 'ProductController@seriesAll');

        $api->group(['middleware' => 'auth:api'], function ($api) {
            $api->get('/logout', 'Auth\AuthController@destroy');
            //个人信息
            $api->get('/examine/info', 'ExamineController@BaseOne');
            $api->post('/examine/add', 'ExamineController@BaseCreate');
            $api->post('/examine/edit', 'ExamineController@BaseUpdate');
            $api->post('/member/edit', 'ExamineController@memberUpdate');
            //购物车
            $api->get('/shopping', 'ShoppingController@BaseLimit');
            //购物车详情
            $api->get('/shopping/info', 'ShoppingController@BaseOne');
            //加入购物车
            $api->get('/addShopping', 'ShoppingController@create');
            //购物车删除
            $api->get('/delShopping', 'ShoppingController@BaseDelete');
            //购物车下单
            $api->post('/addOrder', 'OrderController@addOrder');
            //订单列表
            $api->get('/orderList', 'OrderController@BaseLimit');
            //订单详情
            $api->get('/orderInfo', 'OrderController@BaseOne');
            //订单操作
            $api->get('/editOrder', 'OrderController@BaseUpdate');
            //订单操作
            $api->get('/delOrder', 'OrderController@BaseDelete');
            //打印
            $api->get('/orderInfoPdf', 'OrderController@orderInfoPdf');
            //查定模块
            $api->get('/materiel/list', 'MaterielController@BaseLimit');//列表
            $api->get('/materiel/info', 'MaterielController@BaseOne');//列表
            $api->post('/materiel/add', 'MaterielController@BaseCreate');//新增
            // $api->post('/materiel/edit', 'MaterielController@BaseUpdate');//修改
            $api->get('/materiel/del', 'MaterielController@BaseDelete');//删除
            $api->post('/attachment/add', 'AttachmentController@add');//新增
        });
    });

    //规划后端接口
    $api->group(['namespace' => 'Admin\V1', "prefix" => 'admin'], function ($api) {
        //登录
        $api->post('/login', 'Auth\AuthController@login');
        //需要权限接口
        $api->group(['middleware' => 'auth:admin'], function ($api) {
            //退出接口
            $api->post('/logout', 'Auth\AuthController@destroy');
            //登录用户拥有菜单
            $api->get('/getMenus', 'Auth\AuthController@getMenus');
            //菜单模块
            $api->get('/menus/getMenus', 'Auth\MenuController@getMenus');//列表
            $api->get('/menus/info', 'Auth\MenuController@BaseOne');//详情
            $api->post('/menus/add', 'Auth\MenuController@BaseCreate');//新增
            $api->post('/menus/edit', 'Auth\MenuController@BaseUpdate');//修改
            $api->get('/menus/del', 'Auth\MenuController@BaseDelete');//删除
            //角色模块
            $api->get('/role/list', 'Auth\RolesController@BaseLimit');//列表
            $api->get('/role/info', 'Auth\RolesController@BaseOne');//详情
            $api->post('/role/add', 'Auth\RolesController@BaseCreate');//新增
            $api->post('/role/edit', 'Auth\RolesController@BaseUpdate');//修改
            $api->get('/role/del', 'Auth\RolesController@BaseDelete');//删除
            $api->get('/role/list', 'Auth\RolesController@BaseLimit');//列表
            $api->get('/role/roleList', 'Auth\RolesController@roleList');//列表
            $api->post('/role/setRoleList', 'Auth\RolesController@setRoleList');//列表
            //用户模块
            $api->get('/user/list', 'Auth\UserController@BaseLimit');//列表
            $api->get('/user/info', 'Auth\UserController@BaseOne');//详情
            $api->post('/user/add', 'Auth\UserController@BaseCreate');//新增
            $api->post('/user/edit', 'Auth\UserController@BaseUpdate');//修改
            $api->get('/user/del', 'Auth\UserController@BaseDelete');//删除
            //日志模块
            $api->get('/log/list', 'Auth\Logs@BaseLimit');//列表
            $api->get('/log/info', 'Auth\Logs@BaseOne');//详情
            // $api->get('/log/del', 'Logs@BaseDelete');//删除
            //轮播模块
            $api->get('/slide/list', 'SlideController@BaseLimit');//列表
            $api->get('/slide/info', 'SlideController@BaseOne');//详情
            $api->post('/slide/add', 'SlideController@BaseCreate');//新增
            $api->post('/slide/edit', 'SlideController@BaseUpdate');//修改
            $api->get('/slide/del', 'SlideController@BaseDelete');//删除
            //文章模块
            $api->get('/article/list', 'ArticleController@BaseLimit');//列表
            $api->get('/article/info', 'ArticleController@BaseOne');//详情
            $api->post('/article/add', 'ArticleController@BaseCreate');//新增
            $api->post('/article/edit', 'ArticleController@BaseUpdate');//修改
            $api->get('/article/del', 'ArticleController@BaseDelete');//删除
            //图片文件模块
            $api->get('/attachment/list', 'AttachmentController@BaseLimit');//列表
            $api->get('/attachment/info', 'AttachmentController@BaseOne');//详情
            $api->post('/attachment/add', 'AttachmentController@add');//新增
            $api->post('/attachment/edit', 'AttachmentController@BaseUpdate');//修改
            $api->get('/attachment/del', 'AttachmentController@BaseDelete');//删除
            //品牌模块
            $api->get('/brand/list', 'BrandController@BaseLimit');//列表
            $api->get('/brand/info', 'BrandController@BaseOne');//详情
            $api->post('/brand/add', 'BrandController@BaseCreate');//新增
            $api->post('/brand/edit', 'BrandController@BaseUpdate');//修改
            $api->get('/brand/del', 'BrandController@BaseDelete');//删除
            //分类模块
            $api->get('/category/list', 'CategoryController@BaseLimit');//列表
            $api->get('/category/all', 'CategoryController@BaseAll');//列表
            $api->get('/category/info', 'CategoryController@BaseOne');//详情
            $api->post('/category/add', 'CategoryController@BaseCreate');//新增
            $api->post('/category/edit', 'CategoryController@BaseUpdate');//修改
            $api->get('/category/del', 'CategoryController@BaseDelete');//删除
            //产品系列模块
            $api->get('/series/list', 'SeriesController@BaseLimit');//列表
            $api->get('/series/all', 'SeriesController@BaseAll');//列表
            $api->get('/series/info', 'SeriesController@BaseOne');//详情
            $api->post('/series/add', 'SeriesController@BaseCreate');//新增
            $api->post('/series/edit', 'SeriesController@BaseUpdate');//修改
            $api->get('/series/del', 'SeriesController@BaseDelete');//删除
            //类型模块
            $api->get('/attribute_category/list', 'ProductAttributeCategoryController@BaseLimit');//列表
            $api->get('/attribute_category/all', 'ProductAttributeCategoryController@BaseAll');//列表
            $api->get('/attribute_category/info', 'ProductAttributeCategoryController@BaseOne');//详情
            $api->post('/attribute_category/add', 'ProductAttributeCategoryController@BaseCreate');//新增
            $api->post('/attribute_category/edit', 'ProductAttributeCategoryController@BaseUpdate');//修改
            $api->get('/attribute_category/del', 'ProductAttributeCategoryController@BaseDelete');//删除
            //类型模块
            $api->get('/attribute/list', 'ProductAttributeController@BaseLimit');//列表
            $api->get('/attribute/all', 'ProductAttributeController@BaseAll');//列表
            $api->get('/attribute/info', 'ProductAttributeController@BaseOne');//详情
            $api->post('/attribute/add', 'ProductAttributeController@BaseCreate');//新增
            $api->post('/attribute/edit', 'ProductAttributeController@BaseUpdate');//修改
            $api->get('/attribute/del', 'ProductAttributeController@BaseDelete');//删除
            //商品模块
            $api->get('/product/list', 'ProductController@BaseLimit');//列表
            $api->get('/product/info', 'ProductController@BaseOne');//详情
            $api->post('/product/add', 'ProductController@BaseCreate');//新增
            $api->post('/product/edit', 'ProductController@BaseUpdate');//修改
            $api->get('/product/delete', 'ProductController@delete');//删除
            //会员模块
            $api->get('/member/list', 'MemberController@BaseLimit');//列表
            $api->get('/member/info', 'MemberController@BaseOne');//详情
            $api->get('/member/pass', 'MemberController@pass');//详情
            $api->post('/member/add', 'MemberController@BaseCreate');//新增
            $api->post('/member/edit', 'MemberController@BaseUpdate');//修改
            $api->get('/member/del', 'MemberController@BaseDelete');//删除
            //会员申请模块
            $api->get('/examine/list', 'ExamineController@BaseLimit');//列表
            $api->get('/examine/info', 'ExamineController@BaseOne');//详情
            $api->post('/examine/add', 'ExamineController@BaseCreate');//新增
            $api->post('/examine/edit', 'ExamineController@BaseUpdate');//修改
            $api->get('/examine/del', 'ExamineController@BaseDelete');//删除
            //订单模块
            $api->get('/order/list', 'OrderController@BaseLimit');//列表
            $api->get('/order/info', 'OrderController@BaseOne');//详情
            // $api->post('/order/add', 'OrderController@BaseCreate');//新增
            $api->post('/order/edit', 'OrderController@BaseUpdate');//修改
            $api->get('/order/del', 'OrderController@BaseDelete');//删除
            //店铺模块
            $api->get('/shop/list', 'ShopController@BaseLimit');//列表
            $api->get('/shop/info', 'ShopController@BaseOne');//详情
            $api->post('/shop/add', 'ShopController@BaseCreate');//新增
            $api->post('/shop/edit', 'ShopController@BaseUpdate');//修改
            $api->get('/shop/del', 'ShopController@BaseDelete');//删除
            //配置模块
            $api->get('/config/list', 'ConfigController@BaseLimit');//列表
            $api->get('/config/info', 'ConfigController@BaseOne');//列表
            $api->post('/config/add', 'ConfigController@BaseCreate');//新增
            $api->post('/config/edit', 'ConfigController@BaseUpdate');//修改
            $api->get('/config/del', 'ConfigController@BaseDelete');//删除
            //查定
            $api->get('/materiel/list', 'MaterielController@BaseLimit');//列表
            $api->get('/materiel/info', 'MaterielController@BaseOne');//列表
            $api->post('/materiel/add', 'MaterielController@BaseCreate');//新增
            $api->post('/materiel/edit', 'MaterielController@BaseUpdate');//修改
            $api->get('/materiel/del', 'MaterielController@BaseDelete');//删除
        });
    });
});
// 其中 ： 刷新token 和 注销接口 请求的时候需要以下请求头：
// Authorization  ： Bearer + "your token"
