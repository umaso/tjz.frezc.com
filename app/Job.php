<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    //
    protected $table = 'jobs';
    protected $guarded = ['id','salary', 'description',
        'number','number_applied',
        'visited','time','name',
        'company_id','company_name',
        'active'];
}
