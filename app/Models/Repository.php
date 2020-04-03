<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    protected $fillable = [
        'name', 'owner'
    ];

    public function likes() {
        return $this->hasMany(RepositoryLike::class, 'repository_id', 'id')->where('like', RepositoryLike::TYPE_LIKE);
    }

    public function dislikes() {
        return $this->hasMany(RepositoryLike::class, 'repository_id', 'id')->where('like', RepositoryLike::TYPE_DISLIKE);
    }

    public function currentUserLike(){
        return $this->hasOne(RepositoryLike::class, 'repository_id', 'id')->where('user_id', auth()->user()->id);
    }
}
