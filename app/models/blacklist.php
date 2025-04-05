<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Migrations\BlacklistsMigration;
class Blacklist extends Model {
    public const TABLE = BlacklistsMigration::TABLE;
    public const TYPE_BEARER = ['type' => 'Bearer'];
    public const KEY_TOKEN = ['key' => 'token'];
    // public const BEARER_TOKEN = ['type' => self::TYPE_BEARER['type'], 'key' => self::KEY_TOKEN['key']];

    protected $table = self::TABLE;
    protected $fillable = ['type', 'key', 'value', 'reason', 'expires_at', 'status'];
}



