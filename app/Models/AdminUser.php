<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;   //

class AdminUser extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    public $table = 'admin_users';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        // 'phone',  //注意我在数据表 users 中添加了 phone 字段
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //实现 JWTSubject 以下两个接口函数
    public function getJWTIdentifier(){
        //`在这里插入代码片`
        return $this->getKey();
    }

    public function getJWTCustomClaims(){
        return ['role' => 'admin'];
    }

}
