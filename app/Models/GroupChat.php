<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupChat extends Model
{
    use HasFactory;

    protected $table = 'group_chats';

    public function sender()
    {
        return $this->belongsTo(User::class,'sender','id');
    }
    public function group()
    {
        return $this->belongsTo(Group::class,'group_id','id');
    }
}
