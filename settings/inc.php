<?php
// load constants
require_once __DIR__ . '/constants.php';

// load vendor files
require_once PACKAGE_DIR . '/vendor/autoload.php';

// load settings directory's files
require_once BASE_DIR . '/settings/load.php';
require_once BASE_DIR . '/settings/dotenv.php';

// load utility directory's files from BASE_DIR
require_once BASE_DIR . '/utility/spinner.php';

// load base directory's files
require_once BASE_DIR . '/configs/main.php';
require_once BASE_DIR . '/configs/log.php';
require_once BASE_DIR . '/configs/connection.php';
require_once BASE_DIR . '/configs/db.php';

// load migrations directory's files from BASE_DIR
require_once BASE_DIR . '/migrations/inc.php';

// load app directory's files from BASE_DIR
require_once BASE_DIR . '/app/kernel.php';

// load routes directory's files from BASE_DIR
// require_once BASE_DIR . '/routes/web.php';
// require_once BASE_DIR . '/routes/api.php';

// load app directory controllers files from BASE_DIR
require_once BASE_DIR . '/app/controllers/base-controller.php';
require_once BASE_DIR . '/app/controllers/home-controller.php';

// load app directory helpers files from BASE_DIR
require_once BASE_DIR . '/app/helpers/constants.php';
require_once BASE_DIR . '/app/helpers/resources.php';

// load app directory middleware files from BASE_DIR
require_once BASE_DIR . '/app/middlewares/base-middleware.php'; 
require_once BASE_DIR . '/app/middlewares/auth-middleware.php'; 
require_once BASE_DIR . '/app/middlewares/guest-middleware.php';   

// load app/models directory's files from BASE_DIR
require_once BASE_DIR . '/app/models/inc.php';

// load app/handlers directory's files from BASE_DIR
require_once BASE_DIR . '/app/handlers/request.php';
require_once BASE_DIR . '/app/handlers/response.php';

// load app/auth directory's files from BASE_DIR
require_once BASE_DIR . '/app/auth/base-auth.php';
require_once BASE_DIR . '/app/auth/authenticator.php';

// load app/handlers directory's files from BASE_DIR
require_once BASE_DIR . '/app/handlers/error.php';
require_once BASE_DIR . '/app/handlers/base-router.php';
require_once BASE_DIR . '/app/handlers/router.php';
require_once BASE_DIR . '/app/handlers/routes-register.php';


// require_once BASE_DIR . '/app/models/user-permission.php';




