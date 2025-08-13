<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class TelegramAuthService
{
    
    public function authenticate(array $telegramData)
    {
        $validator = Validator::make($telegramData, [
            'id'        => 'required|numeric',
            'auth_date' => 'required|date_format:U|before:1 day',
            'hash'      => 'required|size:64',
        ]);

        throw_if($validator->fails(), InvalidArgumentException::class);

        $dataToHash = collect(Arr::except($telegramData, 'hash'))
                        ->transform(function ($val, $key) {
                            return "$key=$val";
                        })
                        ->sort()
                        ->join("\n");

        $hash_key = hash('sha256', config('services.telegram.client_secret'), true);
        $hash_hmac = hash_hmac('sha256', $dataToHash, $hash_key);

        throw_if(
            $telegramData['hash'] !== $hash_hmac,
            InvalidArgumentException::class
        );

        return Arr::except($telegramData, ['auth_date', 'hash']);
    }
}
