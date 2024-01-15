<?php

namespace App\Models;

use Illuminate\Http\Request;
use App\Models\BaseModel;

class AdminUsers extends BaseModel
{
    public $table = 'admin_users';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
