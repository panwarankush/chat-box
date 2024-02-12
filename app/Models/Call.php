<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;

    protected $fillable = ['callerId','receiverId','type','callTime','status'];


    public function caller()
    {
        return $this->belongsTo(User::class,'callerId','id');
    }
    public function callee()
    {
        return $this->belongsTo(User::class,'receiverId','id');
    }
}
