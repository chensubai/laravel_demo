<?php

namespace App\Models;

use Illuminate\Http\Request;
use App\Models\BaseModel;

class Product extends BaseModel
{
    public $table = 'pms_product';

    const CREATEDTIME = 'createtime';
    const UPDATEDTIME = 'updatetime';
}
