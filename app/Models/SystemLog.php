<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable =[
        'user', 
        'action', 
        'model', 
        'description'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
