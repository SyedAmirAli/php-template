<?php

namespace App\Migrations;

include_once BASE_DIR . '/migrations/roles.php';
include_once BASE_DIR . '/migrations/users.php';
include_once BASE_DIR . '/migrations/menus.php';
include_once BASE_DIR . '/migrations/blacklists.php';
include_once BASE_DIR . '/migrations/user_roles.php';
include_once BASE_DIR . '/migrations/migrations.php';
include_once BASE_DIR . '/migrations/permissions.php';
include_once BASE_DIR . '/migrations/role_has_permissions.php';

// include schema at the end
include_once BASE_DIR . '/migrations/schema.php';

