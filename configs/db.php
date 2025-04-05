<?php

namespace App\Configs;

use App\Configs\Main;
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();
$capsule->addConnection(Main::getDBConfig());
$capsule->setAsGlobal();
$capsule->bootEloquent();