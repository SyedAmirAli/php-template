<?php

namespace App\Models;

use App\Configs\Log;
use App\Models\Permission;
use App\Models\RoleHasPermission;
use Illuminate\Database\Eloquent\Model;

final class Menu extends Model {
    protected $table = 'menus';
    protected $casts = ['status' => 'boolean'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['title', 'href', 'route', 'icon', 'status', 'order', 'type', 'parent_id'];

    /**
     * Get the parent of the menu item
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent() {
        return $this->belongsTo(self::class, 'parent_id')->whereNull('parent_id');
    }

    /**
     * Get the children of the menu item
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children() {
        return $this->hasMany(Menu::class, 'parent_id')->with('children');
    }
    
    /**
     * Creates a new menu item in the database
     * 
     * @param array $menu The menu item data
     * @param string $type The type of menu item
     * @param int|null $parentId The parent menu item ID
     * @return int The ID of the created menu item
     */ 
    public static function makeMenu(
        array $menu, 
        string $type, 
        ?int $parentId = null
    ): int {
        $title = (isset($menu['title']) && !empty($menu['title'])) ? $menu['title'] : null;
        
        $menu = [
            'type' => $type,
            'title' => $title,
            'parent_id' => $parentId,
            'href' => (isset($menu['href']) && !empty($menu['href'])) ? $menu['href'] : null,
            'route' => (isset($menu['route']) && !empty($menu['route'])) ? $menu['route'] : null,
            'icon' => (isset($menu['icon']) && !empty($menu['icon'])) ? $menu['icon'] : null,
            'order' => (isset($menu['order']) && !empty($menu['order'])) ? $menu['order'] : 1,
            'status' => (isset($menu['status']) && !empty($menu['status'])) ? $menu['status'] : true,
        ];

        // $createMenu = self::firstOrCreate(
        //     ['title' => $title, 'type' => $type],
        //     $menu
        // );
        $createMenu = self::create($menu);
        return $createMenu->id;
    }    
    
    /**
     * Recursively synchronizes menu data from a JSON file into the database
     * 
     * @param string $filePath Path to the JSON file containing menu structure
     * @return array Returns array with 'titles' containing menu paths and 'newMenus' containing created menus
     */
    public static function syncMenuFromFileRecursive(string $filePath): array {
        $menus = file_get_contents($filePath);
        $menus = json_decode($menus, true);

        $titles = [];
        $newMenus = [];
        
        foreach($menus as $type => $menu) {
            foreach($menu as $key => $item) {
                self::processMenuRecursively($item, $type, null, $titles, $newMenus);
            }
        }
        
        $message = "Synced menu from file: {$filePath}. And check logs for more details.";
        $result = compact('titles', 'newMenus', 'message');

        Log::info($result);
        return $result;
    }
    
    /**
     * Processes a menu item recursively and creates a new menu item in the database
     * 
     * @param array $menuItem The menu item data
     * @param string $type The type of menu item
     * @param int|null $parentId The parent menu item ID
     * @param array &$titles The array of titles
     * @param array &$newMenus The array of new menus
     * @param string $path The path of the menu item
     * @return void
     */ 
    private static function processMenuRecursively(
        array $menuItem, 
        string $type, 
        ?int $parentId, 
        array &$titles, 
        array &$newMenus, 
        string $path = ''
    ): void {
        $menuId = self::makeMenu($menuItem, $type, $parentId);
        
        $currentPath = $path ? "{$path}-{$menuId}" : "{$type}-{$menuId}";
        $titles[$currentPath] = $menuItem['title'];
        
        if (isset($menuItem['children']) && is_array($menuItem['children'])) {
            foreach($menuItem['children'] as $child) {
                self::processMenuRecursively($child, $type, $menuId, $titles, $newMenus, $currentPath);
            }
        }
    }
    
    public function permissions() {
        return $this->hasMany(Permission::class, 'menu_id');
    }

    public function roleHasPermissions() {
        return $this->hasMany(RoleHasPermission::class, 'menu_id');
    }

    /**
     * Builds a hierarchical menu structure from a flat collection of menu objects
     * 
     * This function organizes menu items into a tree structure based on parent-child relationships.
     * It first groups menus by their type, then creates a parent-child hierarchy within each type.
     * Menu items with no parent (parent_id is null) become root nodes, while others are attached
     * to their respective parents. If a menu's parent doesn't exist, it's added to the root level.
     * 
     * @param object $menus A collection of menu objects to organize into a hierarchy
     * @return array An associative array where keys are menu types and values are collections of root menu items with their children
     */
    public static function buildMenuHierarchy(object $menus) {
        // group menus by type
        $menusByType = $menus->groupBy('type');
        $result = [];
        
        foreach($menusByType as $type => $typeMenus) {
            // group menus by parent id
            $parentMap = [];
            foreach($typeMenus as $menu) {
                $id = $menu->id;
                if(!isset($parentMap[$id])) {
                    // clone menu to avoid model object change
                    $parentMap[$id] = clone $menu;
                    $parentMap[$id]->children = collect([]);
                }
            }
            
            // create children relation
            $rootMenus = collect();
            foreach($parentMap as $id => $menu) {
                if(is_null($menu->parent_id)) {
                    $rootMenus->push($menu);
                } else {
                    // add child if parent exists
                    if(isset($parentMap[$menu->parent_id])) {
                        $parentMap[$menu->parent_id]->children->push($menu);
                    } else {
                        // add to root if parent not exists
                        $rootMenus->push($menu);
                    }
                }
            }
            
            $result[$type] = $rootMenus->values();
        }
        
        return $result;
    }
}



