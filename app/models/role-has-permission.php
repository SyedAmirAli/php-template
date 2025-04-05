<?php

namespace App\Models;

use App\Models\Menu;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;

final class RoleHasPermission extends Model {
    protected $table = 'role_has_permissions';
    protected $fillable = ['role_id', 'permission_id', 'menu_id', 'status'];
    
    public function role() {
        return $this->belongsTo(Role::class);   
    }

    public function permission() {
        return $this->belongsTo(Permission::class);
    }

    public function menu() {
        return $this->belongsTo(Menu::class);
    }   
}

