<?php

namespace App\Models;

use Illuminate\Http\Request;
use App\Models\BaseModel;

class Roles extends BaseModel
{
    public $table = 'admin_roles';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

}
