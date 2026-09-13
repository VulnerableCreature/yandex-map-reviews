<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'max:1024', 'url', 'regex:#^https?://(www\.)?(yandex\.[a-z]+|maps\.yandex\.[a-z]+)/#i'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.regex' => 'Ссылка должна вести на yandex.ru/maps или maps.yandex.ru.',
        ];
    }
}
