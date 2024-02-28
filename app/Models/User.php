<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'cpf',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function users(){
        return $this->hasMany('App\Models\Transaction');
        return $this->hasMany('App\Models\Category');
    }

    // protected function createdAtFormatted(): User{
    //     return user::make(
    //         get: fn ($value, $attributes) => $attributes['created_at']->format('d-M-Y, H:i'),
    //     );
    // }

    // protected function updatedAtFormatted(): User{
    //     return User::make(
    //         get: fn ($value, $attributes) => $attributes['updated_at']->format('d-M-Y, H:i'),
    //     );
    // }
}
