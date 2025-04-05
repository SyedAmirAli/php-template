<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use App\Configs\Log;

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token', 'deleted_at', 'created_at', 'updated_at'];

    /**
     * Define the many-to-many relationship between User and Role models
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles() {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Get all roles with their associated permissions for this user
     * Eager loads the permissions relationship for better performance
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function rolesHasPermissions() {
        return $this->roles()->with('permissions');
    }

    /**
     * Check if the user has a specific role by its code
     * 
     * @param string $roleCode The unique code of the role to check
     * @return bool True if the user has the role, false otherwise
     */
    public function hasRole(string $roleCode) {
        return $this->roles->contains('code', $roleCode);
    }

    /**
     * Check if the user has any or all of the specified roles
     * 
     * @param array $roleCodes Array of role codes to check
     * @param bool $strict When true, user must have ALL specified roles; when false, user must have AT LEAST ONE role
     * @return bool True if the condition is met, false otherwise
     */
    public function hasRoles(array $roleCodes, bool $strict = false): bool {
        $userRoleCodes = $this->roles->pluck('code')->toArray();
        
        // Strict means all specified roles must be attached to the user
        if($strict) return count(array_intersect($userRoleCodes, $roleCodes)) === count($roleCodes);
        
        // Non-strict means at least one of the specified roles must be attached
        foreach($roleCodes as $roleCode) {
            if(in_array($roleCode, $userRoleCodes)) return true;
        }
        return false;
    }

    /**
     * Check if the user has a specific permission by its code
     * Searches through all permissions from all roles assigned to the user
     * 
     * @param string $permissionCode The unique code of the permission to check
     * @return bool True if the user has the permission, false otherwise
     */
    public function hasPermission(string $permissionCode): bool {
        return $this->rolesHasPermissions->pluck('permissions')->flatten(1)->contains('name', $permissionCode);
    }

    /**
     * Check if the user has any or all of the specified permissions
     * 
     * @param array $permissionCodes Array of permission codes to check
     * @param bool $strict When true, user must have ALL specified permissions; when false, user must have AT LEAST ONE permission
     * @return bool True if the condition is met, false otherwise
     */
    public function hasPermissions(array $permissionCodes, bool $strict = false): bool {
        $userPermissionCodes = collect($this->rolesHasPermissions)
            ->pluck('permissions')
            ->flatten(1)
            ->pluck('name')
            ->unique() // Remove duplicates
            ->values()
            ->toArray();
    
        if ($strict) return empty(array_diff($permissionCodes, $userPermissionCodes));
    
        return (bool) array_intersect($permissionCodes, $userPermissionCodes);
    }   
}