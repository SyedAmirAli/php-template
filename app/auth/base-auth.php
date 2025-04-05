<?php

namespace App\Auth;

use App\Models\User;
use App\Configs\Log;
use App\Handlers\Response;


class BaseAuth extends Response {
    private static string $secretKey = '217c1efee157d7621b1b6468a0503dce7f07c0fba8be0e0287a39b7845a47104';

    public static function encode($payload, string $algo = 'HS256'): string {
        $header = [
            'alg' => $algo,
            'typ' => 'JWT'
        ];

        if(is_array($payload) || is_object($payload)) {
            $payload = json_encode($payload);
        }

        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode($payload);

        $signature = hash_hmac('sha256', "{$base64UrlHeader}.{$base64UrlPayload}", self::$secretKey, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return "{$base64UrlHeader}.{$base64UrlPayload}.{$base64UrlSignature}";
    }

    public static function decode(string $jwt) {
        [$headerEncoded, $payloadEncoded, $signatureEncoded] = explode('.', $jwt);

        $dataToVerify = "{$headerEncoded}.{$payloadEncoded}";
        $signature = self::base64UrlDecode($signatureEncoded);
        $valid = hash_equals($signature, hash_hmac('sha256', $dataToVerify, self::$secretKey, true));

        if (!$valid) return null;

        $data = self::base64UrlDecode($payloadEncoded);
        $decoded = json_decode($data, true); //json_decode(self::base64UrlDecode($payloadEncoded), true);

        if(json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $data;
    }

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}