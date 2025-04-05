<?php

namespace App\Auth;

use App\Configs\Log;
use App\Configs\Main;
use App\Auth\BaseAuth;
use App\Models\User;
use App\Models\Blacklist;
use Illuminate\Database\Eloquent\Collection;
use App\Handlers\AuthResponse;
use App\Handlers\Response;
use Carbon\Carbon;

class Authenticator extends BaseAuth {
    public static Collection|User|null $user = null;
    public static ?int $id = null;
    public static ?string $email = null;
    public static $tokenTimeValidation = true;
    public static ?AuthResponse $credentials = null;
    
    public static function register(array $credentials, bool $isLogin = true, bool $isValidate = true): AuthResponse {
        if(!isset($credentials['email']) || !isset($credentials['password'])) { 
            return new AuthResponse(message: 'Email and password are required!', status: 'error', code: AuthResponse::REGISTER_FAILED);
        }

        if(User::where('email', $credentials['email'])->exists()) {
            return new AuthResponse(message: 'User already exists!', status: 'error', code: AuthResponse::REGISTER_FAILED);
        }

        $user = User::create($credentials);
        if($isLogin) return self::login($credentials, $isValidate);
        return new AuthResponse($user, message: 'User created successfully!', status: 'success', code: AuthResponse::REGISTER_SUCCESS);
    }

    public static function login(array $credentials, bool $isValidate = true): AuthResponse|array {
        if(!isset($credentials['email']) || !isset($credentials['password'])) {
            return new AuthResponse(message: 'Email and password are required!', status: 'error', code: AuthResponse::LOGIN_FAILED);
        }

        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user || !password_verify($credentials['password'], $user->password)) {
            return new AuthResponse(message: 'Invalid credentials!', status: 'error', code: AuthResponse::LOGIN_FAILED);
        }

        $response = new AuthResponse(
            user: $user, 
            message: 'Login successful!', 
            status: 'success', 
            code: AuthResponse::LOGIN_SUCCESS, 
            token_type: Response::TOKEN_TYPE_BEARER,
        );

        // $validate = null;
        $token = self::encode([
            'id' => $user->id,
            'email' => $user->email,
            'token_type' => $response->token_type,
            'expires_in' => $response->expires_in,
            'token_created_at' => $response->token_created_at,
            'token_expires_at' => $response->token_expires_at,
            'expires_time_unit' => $response->expires_time_unit,
        ]);

        // if($isValidate) self::validateToken($token);
        $response->token = $token;

        // set session
        ini_set('session.gc_maxlifetime', $response->expires_in);

        // if session not started, start it
        if(session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION[self::COOKIE_AUTH_TOKEN_KEY] = $token;

        // set cookie
        setcookie(self::COOKIE_AUTH_TOKEN_KEY, $token, $response->expires_in, '/');

        return $response;
    }

    public static function validateToken(?string $token = null): AuthResponse {
        if(empty($token) && isset($_COOKIE[self::COOKIE_AUTH_TOKEN_KEY])) {
            $token = $_COOKIE[self::COOKIE_AUTH_TOKEN_KEY];
        } else Log::info('Token from cookie: ' . $token);

        if(empty($token) && isset($_SESSION[self::COOKIE_AUTH_TOKEN_KEY])) {
            $token = $_SESSION[self::COOKIE_AUTH_TOKEN_KEY];
        } else Log::info('Token from session: ' . $token);

        if(!$token || empty($token)) {
            return new AuthResponse(message: 'Token is required!', status: 'error', code: AuthResponse::VALIDATE_TOKEN_FAILED);
        }

        if($blacklist = Blacklist::where('value', $token)
            ->where( Blacklist::KEY_TOKEN)
            ->where(Main::STATUS_CHECK)
            ->first()
        ) {
            return new AuthResponse(message: "Token is blacklisted by {$blacklist->reason}!", status: 'error', code: AuthResponse::VALIDATE_TOKEN_FAILED);
        }

        $tokenResult = self::decode($token);

        if(!is_array($tokenResult) && !isset($tokenResult['id']) && !isset($tokenResult['token_created_at']) && !isset($tokenResult['token_expires_at'])) {
            return new AuthResponse(message: 'Invalid token!', status: 'error', code: AuthResponse::VALIDATE_TOKEN_FAILED);
        }

        if(self::$tokenTimeValidation) {
            if(!Main::validateTime($tokenResult['token_created_at'], $tokenResult['token_expires_at'])) {
                return new AuthResponse(message: 'Token expired!', status: 'error', code: AuthResponse::VALIDATE_TOKEN_FAILED);
            }
        }

        $result = new AuthResponse();
        $user = User::with('roles')->find($tokenResult['id']);

        if($user && !is_null($user)) {
            $result->user = $user;
            self::$user = $result->user;
            self::$id = $result->user->id;
            self::$email = $result->user->email;
        }

        if(isset($tokenResult['expires_in'])) $result->expires_in = $tokenResult['expires_in'];
        if(isset($tokenResult['token_created_at'])) $result->token_created_at = $tokenResult['token_created_at'];
        if(isset($tokenResult['token_expires_at'])) $result->token_expires_at = $tokenResult['token_expires_at'];
        if(isset($tokenResult['expires_time_unit'])) $result->expires_time_unit = $tokenResult['expires_time_unit'];
        if(isset($tokenResult['token_type'])) $result->token_type = $tokenResult['token_type'];
        if(isset($tokenResult['token'])) $result->token = $tokenResult['token'];
        if(isset($tokenResult['message'])) $result->message = $tokenResult['message'];
        if(isset($tokenResult['status'])) $result->status = $tokenResult['status'];
        if(isset($tokenResult['code'])) $result->code = $tokenResult['code'];

        self::$credentials = $result;
        return $result;
    }

    public static function logout(?string $token = null): bool {
        if($token) {
            $blacklist = Blacklist::create([
                'type' => Blacklist::TYPE_BEARER,
                'key' => Blacklist::KEY_TOKEN,
                'value' => $token,
                'reason' => 'logout',
                'expires_at' => Carbon::now(APP_TIMEZONE)->toIso8601ZuluString(),
            ]);
        }
        // remove session
        if(isset($_SESSION[self::COOKIE_AUTH_TOKEN_KEY])) unset($_SESSION[self::COOKIE_AUTH_TOKEN_KEY]);

        // remove cookie
        setcookie(self::COOKIE_AUTH_TOKEN_KEY, '', time() - 3600, '/');

        self::$credentials = null;
        return true;
    }

    public static function getCredentials(): ?AuthResponse {
        if(!self::$credentials) self::validateToken();
        if(!self::$credentials) return null;

        return self::$credentials;
    }

    public static function getUser(): ?User {
        $credentials = self::getCredentials();
        if(!$credentials) return null;

        return $credentials->user;
    }

    public static function getId(): ?int {
        $credentials = self::getCredentials();
        if(!$credentials) return null;

        return $credentials->user?->id ?? null;
    }

    public static function getEmail(): ?string {
        $credentials = self::getCredentials();
        if(!$credentials) return null;

        return $credentials->user?->email ?? null;
    }

    public static function getRoles(bool $codesOnly = true): null|array|Collection {
        $credentials = self::getCredentials();
        if(!$credentials) return null;

        $roles = $credentials->user?->roles ?? null;
        if(!$roles) return null;

        if($codesOnly) return $roles->pluck('code')->toArray();

        return $roles;
    }
}
