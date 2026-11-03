<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

class EncryptedArray implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            // Read legacy plaintext descriptors once so existing records remain compatible.
            $legacy = json_decode($value, true);
            return is_array($legacy) ? $legacy : null;
        }
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return Crypt::encryptString(json_encode($value, JSON_THROW_ON_ERROR));
    }
}
