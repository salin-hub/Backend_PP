<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'preferred_category_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'role' => 'user', // Default role
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }
    public function preferredCategory()
    {
        return $this->belongsTo(Category::class, 'preferred_category_id');
    }
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $lastUser = self::orderBy('id', 'desc')->first();
            $lastId = $lastUser ? intval($lastUser->id) : 11110;
            $model->id = str_pad($lastId + 1, 7, '0', STR_PAD_LEFT);
        });
    }

}
