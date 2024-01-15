<?php

namespace App\Models;

use Illuminate\Http\Request;
use App\Models\BaseModel;

class Menus extends BaseModel
{
    public $table = 'admin_menus';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
