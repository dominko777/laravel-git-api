<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositoryLike extends Model
{
    const TYPE_LIKE = 1;
    const TYPE_DISLIKE = 0;

    protected $fillable = [
        'user_id', 'repository_id', 'like'
    ];
}
