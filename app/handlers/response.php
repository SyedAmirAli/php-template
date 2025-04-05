<?php

namespace App\Handlers;
use App\Configs\Main;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class Response extends Main {
    public const TOKEN_TYPE_BEARER = 'Bearer';
    public const TOKEN_TYPE_SESSION = 'Session';
    public const COOKIE_AUTH_TOKEN_KEY = 'AUTH_TOKEN';
}


class AuthResponse extends Response {
    public const LOGIN_SUCCESS = 'LOGIN_SUCCESS';
    public const REGISTER_SUCCESS = 'REGISTER_SUCCESS';
    public const REGISTER_FAILED = 'REGISTER_FAILED';
    public const LOGIN_FAILED = 'LOGIN_FAILED';
    public const VALIDATE_TOKEN_SUCCESS = 'VALIDATE_TOKEN_SUCCESS';
    public const VALIDATE_TOKEN_FAILED = 'VALIDATE_TOKEN_FAILED';
    public string $message;
    public string $status;
    public ?string $code;
    public string $token;
    public string $token_type;
    public int $expires_in;
    public string $token_created_at;
    public string $token_expires_at;
    public User|Collection $user;
    public string $expires_time_unit;

    public function __construct(
        Collection|User|null $user = null,
        ?string $token = null, 
        ?string $token_type = null, 
        ?int $expires_in = null, // expires in seconds
        ?string $message = null, 
        ?string $status = null, 
        ?string $code = null,
        ?string $token_created_at = null,
    ) {
        
        if($code) $this->code = $code;
        if($message) $this->message = $message;
        if($status) $this->status = $status;
        if($token) $this->token = $token;
        if($token_type) $this->token_type = $token_type;
        if($expires_in) $this->expires_in = $expires_in;
        if($user) $this->user = $user;

        if (isset($this->code) && $this->code == self::LOGIN_SUCCESS) {
            $this->expires_in = is_numeric($expires_in) ? $expires_in : 3600;
            $this->expires_time_unit = 'second';

            if($token_created_at) {
                $this->token_created_at = Carbon::parse($token_created_at, APP_TIMEZONE)->toIso8601ZuluString($this->expires_time_unit);
            } else {
                $this->token_created_at = Carbon::now(APP_TIMEZONE)->toIso8601ZuluString($this->expires_time_unit);
            }

            $this->token_expires_at = Carbon::now(APP_TIMEZONE)->addSeconds($this->expires_in)->toIso8601ZuluString($this->expires_time_unit);
        }        
    }
}