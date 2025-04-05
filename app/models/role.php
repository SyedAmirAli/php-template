<?php

namespace App\Models;

use Exception;
use App\Configs\Log;
use App\Models\UserRole;
use App\Models\Permission;
use App\Models\RoleHasPermission;
use Illuminate\Database\Eloquent\Model;

final class Role extends Model {
    protected $table = 'roles';
    protected $fillable = ['name', 'code', 'description', 'status'];

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public function menus() {
        return $this->hasManyThrough(
            Menu::class,
            RoleHasPermission::class,
            'role_id',
            'id',
            'id',
            'menu_id'
        );
    }
    
    public function syncMenusToPermission(array $menuIds) {
        // Get existing permissions to avoid unnecessary detaches
        // $existingPermissions = $this->permissions()->get();

        // Get existing menu IDs to avoid unnecessary detaches
        $existingMenuIds = RoleHasPermission::where('role_id', $this->id)
            ->pluck('menu_id')
            ->toArray();
        
        // Find menu IDs to remove and detach only those
        $menuIdsToRemove = array_diff($existingMenuIds, $menuIds);
        if (!empty($menuIdsToRemove)) {
            $permissionIdsToRemove = RoleHasPermission::where('role_id', $this->id)
                ->whereIn('menu_id', $menuIdsToRemove)
                ->pluck('permission_id')
                ->toArray();
            
            // Detach only the permissions that need to be removed
            if (!empty($permissionIdsToRemove)) {
                $this->permissions()->detach($permissionIdsToRemove);
            }
        }

        // new synced permissions and role_has_permissions
        $newSynced = [];

        // Find menu IDs to add (only process new ones)
        $menuIdsToAdd = array_diff($menuIds, $existingMenuIds);
        foreach ($menuIdsToAdd as $menuId) {
            $this->addMenuToRole($menuId, $this->id, true, $newSynced);
        }

        Log::debug('Synced menus to role: ', compact('menuIds', 'existingMenuIds', 'menuIdsToRemove', 'menuIdsToAdd', 'newSynced'));
        return $this;
    }

    public function addMenuToRole(int $menuId, ?int $roleId = null, bool $status = true, array &$created = []) {
        try {
            // 1. create new record in permissions table or get existing one
            $permission = Permission::firstOrCreate(
                [
                    'role_id' => $roleId ?? $this->id,
                    'name' => self::getPermissionName($menuId)
                ],
                [
                    'status' => $status
                ]
            );
            
            // 2. then insert to role_has_permissions table or get existing one
            $roleHasPermission = RoleHasPermission::firstOrCreate(
                [
                    'role_id' => $roleId ?? $this->id,
                    'permission_id' => $permission->id,
                    'menu_id' => $menuId
                ],
                [
                    'status' => $status
                ]
            );

            $created[] = compact('permission', 'roleHasPermission');
        } catch (Exception $e) {
            $message = 'Error syncing menus to role: ' . $e->getMessage();
            Log::error($message);
            throw new Exception($message);
        }

        return $this;
    }

    public function removeMenuFromRole(int $menuId, ?int $roleId = null, array &$created = []) {
        try {
            $permissions = Permission::where('name', self::getPermissionName($menuId))
                ->where('role_id', $roleId ?? $this->id)
                ->get();
            
            if($permissions->count() > 0) {
                $roleHasPermissions = RoleHasPermission::whereIn('permission_id', $permissions->pluck('id'))
                    ->where('role_id', $roleId ?? $this->id)
                    ->where('menu_id', $menuId)
                    ->get();

                Permission::forceDestroy($permissions);
                if($roleHasPermissions->count() > 0) RoleHasPermission::forceDestroy($roleHasPermissions);

                $created[] = compact('permissions', 'roleHasPermissions');
                Log::debug('Removed menu from role: ', compact('permissions', 'roleHasPermissions'));
            } else Log::debug('No permissions found for role: ' . $roleId ?? $this->id);
            
        } catch (Exception $e) {
            $message = 'Error removing menu from role: ' . $e->getMessage();
            Log::error($message);
        }

        return $this;
    }

    public function userRoles() {
        return $this->hasMany(UserRole::class, 'role_id');
    }

    private static function getPermissionName(int $menuId) {
        return "permission_menu_id_{$menuId}";
    }
}

