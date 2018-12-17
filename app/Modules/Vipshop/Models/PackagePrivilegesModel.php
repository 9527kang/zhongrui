<?php

namespace App\Modules\Vipshop\Models;

use Illuminate\Database\Eloquent\Model;

class PackagePrivilegesModel extends Model
{
    
    protected $table = 'package_privileges';

    protected $primaryKey = 'id';

    protected $fillable = [

        'package_id', 'privileges_id','rule'

    ];
}