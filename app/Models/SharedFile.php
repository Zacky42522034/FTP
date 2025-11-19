<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedFile extends Model
{
    public function from()
    {
        return $this->belongsTo(User::class, 'from_user');
    }

    protected $fillable = [
        'from_user',
        'to_email',
        'name',
        'type',
        'size',
        'status',
        'desc',
        'favorite'
    ];
}
