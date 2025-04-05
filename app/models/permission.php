<?php

namespace App\Models;

use App\Models\Role;
use App\Models\RoleHasPermission;
use Illuminate\Database\Eloquent\Model;

final class Permission extends Model {
    protected $table = 'permissions';
    protected $fillable = ['role_id', 'name', 'status'];
    
    public function role() {
        return $this->belongsTo(Role::class);
    }   
    
    public function menu() {
        return $this->hasOneThrough(Menu::class, RoleHasPermission::class, 'permission_id', 'id', 'id', 'menu_id');
    }
    // return $this->belongsToThrough(Menu::class, RoleHasPermission::class, 'permission_id', 'id', 'id', 'menu_id');
}

