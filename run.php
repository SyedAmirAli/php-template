<?php

define('CLI_RUNNER', true);

require_once __DIR__ . '/settings/inc.php';

use App\Migrations\Schema;
use App\Models\User;
use App\Models\Menu;
use App\Models\Role;
use App\Utility\Spinner;
use App\Auth\Authenticator;
use App\Configs\Main;   
use Carbon\Carbon;
// $spinner = new Spinner('Migrating: ');
// $spinner->start();

$user = fn () => User::firstOrCreate([
    'name' => 'John Doe',
    'email' => 'john@doe.com',
    'password' => password_hash('john@doe.com', PASSWORD_DEFAULT) // can i use bcrypt?
]);

// function testUserWithRolesAndMenus() {
function syncMenusToRole(array $role, array $menuIds, bool $isEcho = true) {
    $role = Role::with('menus')->firstOrCreate(
        ['code' => $role['code']], 
        ['name' => $role['name'], 'status' => $role['status']]
    );
    $role->syncMenusToPermission($menuIds);
    // $role->removeMenuFromRole($menuIds[0]);
    // $role->addMenuToRole($menuIds[0]);

    // $result = [
    //     'role' => $role,
    //     // 'syncMenusToPermission' => $role->syncMenusToPermission($menuIds),
    //     // 'addMenuToRole' => $role->addMenuToRole($menuIds[0]),
    //     // 'removeMenuFromRole' => $role->removeMenuFromRole($menuIds[0]),
    // ];
    if ($isEcho) {
        echo json_encode($role, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
    }
    return $role;
}
function createUser(array $roleIds = [], array $user = [
    'name' => 'John Doe',
    'email' => 'john@doe.com',
    'password' => 'john@doe.com'
]): User {
    $user = User::with('roles.menus')->firstOrCreate(
        [
            'email' => $user['email']
        ],
        [
            'name' => $user['name'],
            'password' => password_hash($user['password'], PASSWORD_DEFAULT)
        ]
    );

    if(!empty($roleIds)) $user->roles()->sync($roleIds);
    return $user;
} 

$demoUser = [
    'name' => 'John Doe',
    'email' => 'john@doe.com',
    'password' => 'john@doe.com'
];


// const TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXNzYWdlIjoiTG9naW4gc3VjY2Vzc2Z1bCEiLCJzdGF0dXMiOiJzdWNjZXNzIiwiY29kZSI6IkxPR0lOX1NVQ0NFU1MiLCJ0b2tlbl90eXBlIjoiQmVhcmVyIiwiZXhwaXJlc19pbiI6MzYwMCwidG9rZW5fY3JlYXRlZF9hdCI6IjIwMjUtMDQtMDVUMDY6MTA6MjBaIiwidG9rZW5fZXhwaXJlc19hdCI6IjE5NzAtMDEtMDFUMDA6MDA6MDBaIiwidXNlciI6eyJpZCI6MSwibmFtZSI6IkpvaG4gRG9lIiwiZW1haWwiOiJqb2huQGRvZS5jb20iLCJzdGF0dXMiOjF9fQ.eXlMqEsTZcTkjKo_Zr4EVBmJuKcbNTkqq4dXeCc540A';

// $user = createUser([1, 2, 3, 4], $demoUser);


// $login = Authenticator::login($demoUser);
$auth = 0;//Auth::decode(TOKEN); 

// echo json_encode(compact('login', 'auth'), 128) . PHP_EOL;
// echo json_encode(createUser([1, 2]), 128) . PHP_EOL;


$tokenFromCookie = [
    // 'token' => $_COOKIE[Authenticator::COOKIE_AUTH_TOKEN_KEY] ?? null,
    // 'session' => $_SESSION[Authenticator::COOKIE_AUTH_TOKEN_KEY] ?? null
];

/* class Time {
    public static function isoTimeString(string|int|null $timestamp = null, string $format = 'Y-m-d\TH:i:s\Z', string $timezone = APP_TIMEZONE): string {
        $tz = new DateTimeZone($timezone);
    
        if (is_string($timestamp)) {
            $dt = new DateTime($timestamp, $tz);
        } elseif (is_int($timestamp)) {
            $dt = (new DateTime('@' . $timestamp))->setTimezone($tz);
        } else {
            $dt = new DateTime('now', $tz);
        }
    
        return $dt->format($format);
    }  

    public static function validateTime(int|string $startTime, int|string $endTime): bool {
        if(is_int($startTime)) $startTime = self::isoTimeString($startTime);
        if(is_int($endTime)) $endTime = self::isoTimeString($endTime);

        $startTime = new DateTime($startTime);
        $endTime = new DateTime($endTime);
        return $startTime < $endTime;
    }

    public static function validateExpiredTimeToNow(int|string $time): bool {
        return self::validateTime( 'now', $time);
    }
} */

// $token_created_at = "2025-04-05T18:26:32Z";
// // Parse with explicit UTC timezone then convert to local timezone
// echo "UTC time: " . Carbon::parse($token_created_at)->format('Y-m-d H:i:s') . PHP_EOL;
// echo "Local time: " . Carbon::parse($token_created_at)->tz(APP_TIMEZONE)->format('Y-m-d H:i:s') . PHP_EOL;


// $token_expires_at = "2025-04-05T19:04:56Z";

// $token_created_at = Carbon::parse($token_created_at);
// $token_expires_at = Carbon::parse($token_expires_at);

// // check the create and expires time between the current time
// if($token_created_at->isAfter(Carbon::now()) && $token_expires_at->isBefore(Carbon::now())) {
//     echo "Token is expired" . PHP_EOL;
// } else {
//     echo "Token is not expired" . PHP_EOL;
// }

// echo 'Current Time: ' . Carbon::now()->format('Y-m-d H:i:s') . PHP_EOL;
// // echo time difference between the create and expires time
// echo $token_expires_at->format('Y-m-d H:i:s') . PHP_EOL;
// echo $token_created_at->diffForHumans() . PHP_EOL;
// echo $token_expires_at->diffForHumans() . PHP_EOL;

// echo $token_created_at->format('Y-m-d H:i:s') . ' < ' . $token_expires_at->format('Y-m-d H:i:s') . PHP_EOL;

// $status1 = Main::validateExpiredTimeToNow(/* "2025-04-05T15:14:55Z", */ "2025-04-05T16:14:55Z");
// $status2 = Main::validateExpiredTimeToNow(/* "2025-04-05T17:34:41Z", */ "2025-04-05T18:34:41Z");
// $status3 = Main::validateExpiredTimeToNow("2025-04-05T18:34:41Z");
// echo json_encode(compact('status1', 'status2', 'status3'), 128) . PHP_EOL;

// echo Schema::migrateBlacklists('alterReasonColumn') . PHP_EOL;

// Authenticator::validateToken(TOKEN);
// $validate = Authenticator::$id; //Auth::validateToken(TOKEN);

// echo json_encode(compact('validate', 'login', 'tokenFromCookie'), 128) . PHP_EOL;

// echo "Input your token: ";
// $token = trim(fgets(STDIN));

// $decode = Authenticator::decode($token);
// $validate = Authenticator::validateToken($token);
// echo json_encode(compact('decode', 'validate'), 128) . PHP_EOL;

// echo json_encode(Authenticator::validateToken($token), 128) . PHP_EOL;

// echo json_encode(Role::with('menus')->get(), 128) . PHP_EOL;
/* 
syncMenusToRole(
    [
        'code' => 'admin',
        'name' => 'Administrator',
        'status' => true
    ], 
    // [6, 5, 1]
    [1, 2, 3, 4, 15, 6, 17, 8, 9, 33]
);

syncMenusToRole(
    [
        'code' => 'bd-user',
        'name' => 'Bangladeshi User',
        'status' => true
    ], 
    [1, 12, 3, 14, 5, 16, 7, 18, 25, 30]
);

syncMenusToRole(
    [
        'code' => 'test-role',
        'name' => 'Test Role',
        'status' => true
    ], 
    [100, 101, 102, 103]
);

syncMenusToRole(
    [
        'code' => 'test-role-2',
        'name' => 'Test Role 2',
        'status' => true
    ], 
    [100, 101, 102, 105]
); */

// echo json_encode(Menu::buildMenuHierarchy($user->roles->pluck('menus')->flatten()), 128) . PHP_EOL;

// echo json_encode(Menu::syncMenuFromFileRecursive(
//     "C:\\Users\\rabby\\OneDrive\\Desktop\\user-roles-and-menu-management-php\\test\\menus.json"
// ), 128) . PHP_EOL;

// sleep(10);
// echo json_encode($user(), 128) . PHP_EOL;

// echo Schema::migrateMigrations() . PHP_EOL;

// echo Schema::migrateRoles('up') . PHP_EOL;
// echo Schema::migratePermissions('fresh') . PHP_EOL;
// echo Schema::migrateUsers('up') . PHP_EOL;
// echo Schema::migrateUserRoles('up') . PHP_EOL;
// echo Schema::migrateRoleHasPermissions('fresh') . PHP_EOL;
// echo Schema::migrateMenus('fresh') . PHP_EOL;

// $spinner->stop();

// echo Schema::migrateUsers('up') . PHP_EOL;
// echo Schema::migrateDownUsers() . PHP_EOL;
// echo Schema::migrateFreshUsers() . PHP_EOL;
// $scan = scandir(BASE_DIR . '/migrations');
// echo json_encode($scan, 128) . PHP_EOL;


// echo 'PACKAGE_DIR: ' . PACKAGE_DIR . PHP_EOL;
// echo 'ROOT_DIR: ' . ROOT_DIR . PHP_EOL;
// echo 'BASE_DIR: ' . BASE_DIR . PHP_EOL . PHP_EOL;

// // database credentials
// echo 'DB_HOST: ' . DB_HOST . PHP_EOL;
// echo 'DB_USER: ' . DB_USER . PHP_EOL;
// echo 'DB_PASS: ' . DB_PASS . PHP_EOL;
// echo 'DB_NAME: ' . DB_NAME . PHP_EOL . PHP_EOL;

// // log paths
// echo 'LOG_PATH: ' . LOG_PATH . PHP_EOL;
// echo 'JSON_LOG_PATH: ' . JSON_LOG_PATH . PHP_EOL;
// echo 'CACHE_PATH: ' . CACHE_PATH . PHP_EOL;
// echo 'SESSION_PATH: ' . SESSION_PATH . PHP_EOL;
// echo 'UPLOAD_PATH: ' . UPLOAD_PATH . PHP_EOL . PHP_EOL;


// return Authenticator::getRoles(false);
// return $request;
// $token = isset($request->headers['Authorization']) ? str_replace('Bearer ', '', $request->headers['Authorization']) : null;
// return Authenticator::validateToken($token);
// $user = User::with('roles.menus')->find($id);
// $rawMenus = $user->roles->pluck('menus')->flatten();
// $user->roles->makeHidden(['menus', 'pivot']);
// $roles = $user->roles->pluck('code')->flatten();
// foreach($user->roles as $role) {
//     unset($role->menus);
//     unset($role->pivot);
// }
// unset($user->roles);
// $user->makeHidden('roles');

// $checkRoles = [
//     'hasRole' => $user->hasRole('admin'),
    
//     'invalid role check - admin2' => $user->hasRole('admin2'),
//     'valid roles check - admin, bd-user' => $user->hasRoles(['admin', 'bd-user']),
    
//     'invalid roles check - admin, bd-user2' => $user->hasRoles(['admin', 'bd-user2']),
//     'valid roles check - admin3, bd-user3 (non-strict)' => $user->hasRoles(['admin3', 'bd-user3'], false),

//     'permission_menu_id_2' => $user->hasPermission('permission_menu_id_2'),
//     // 'invalid permission check - permission_menu_id_2/permission_menu_id_3' => $user->hasPermissions(['permission_menu_id_2', 'permission_menu_id_3']),
//     // 'valid permissions check - permission_menu_id_2/permission_menu_id_3 (non-strict)' => $user->hasPermissions(['permission_menu_id_2', 'permission_menu_id_3'], false),
//     'permission_menu_id_2/permission_menu_id_3 (strict)' => $user->hasPermissions(['permission_menu_id_2', 'permission_menu_id_3']),
//     'permission_menu_id_2/permission_menu_id_333 (strict)' => $user->hasPermissions(['permission_menu_id_2', 'permission_menu_id_333']),
//     'permission_menu_id_2/permission_menu_id_333 (non-strict)' => $user->hasPermissions(['permission_menu_id_2', 'permission_menu_id_333'], false),
// ];




















    

const TOKENS = [
    'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiZW1haWwiOiJqb2huQGRvZS5jb20iLCJ0b2tlbl90eXBlIjoiQmVhcmVyIiwiZXhwaXJlc19pbiI6MzYwMCwidG9rZW5fY3JlYXRlZF9hdCI6IjIwMjUtMDQtMDVUMTg6MjY6MzJaIiwidG9rZW5fZXhwaXJlc19hdCI6IjIwMjUtMDQtMDVUMTk6MjY6MzJaIiwiZXhwaXJlc190aW1lX3VuaXQiOiJzZWNvbmQifQ.ICa59nwfMCRW_F6-FXXf3tc_E7AEQHu-uaZOGFH2ThU',
    'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiZW1haWwiOiJqb2huQGRvZS5jb20iLCJ0b2tlbl90eXBlIjoiQmVhcmVyIiwiZXhwaXJlc19pbiI6MzYwMCwidG9rZW5fY3JlYXRlZF9hdCI6IjIwMjUtMDQtMDVUMTU6MTQ6NTVaIiwidG9rZW5fZXhwaXJlc19hdCI6IjIwMjUtMDQtMDVUMTY6MTQ6NTVaIiwiZXhwaXJlc190aW1lX3VuaXQiOiJzZWNvbmRzIn0.W3q7ql06Tw5PIhs2x0KVmucIh8oz5afiqkd5TPDJYO0'
];

$validate = Authenticator::validateToken(TOKENS[0]);
// echo json_encode($validate, 128) . PHP_EOL . PHP_EOL;

echo Authenticator::$id . PHP_EOL;

echo Authenticator::getEmail() . PHP_EOL;

echo json_encode(Authenticator::getRoles()) . PHP_EOL;
echo Authenticator::getUser()->hasRole('admin') ? 'true' : 'false' . PHP_EOL;

// echo json_encode(Authenticator::getRoles(false), 128) . PHP_EOL;
