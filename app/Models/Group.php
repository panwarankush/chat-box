<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [ "name","admin_id","image","description"];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function groupReceivedChats()
    {
        return $this->hasMany(GroupChat::class, 'group_id','id');
    }
}
