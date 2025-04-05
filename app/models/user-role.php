<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class UserRole extends Model {
    protected $table = 'user_roles';
    protected $fillable = ['user_id', 'role_id', 'status'];
    
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }
}

