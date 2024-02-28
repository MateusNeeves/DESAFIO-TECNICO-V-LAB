<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'value',
        'type',
        'id_user',
        'id_category',
    ];

    public function Categories(){
        return $this->belongsTo('App\Models\User');
    }
}
