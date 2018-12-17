<?php

namespace App\Modules\Manage\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FastTaskModel extends Model
{
    
    protected $table = 'fast_task';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'title',
        'desc',
        'uid',
        'task_id',
        'task_type',
        'mobile',
        'status',
        'created_at',
		'updated_at'
    ];


}
