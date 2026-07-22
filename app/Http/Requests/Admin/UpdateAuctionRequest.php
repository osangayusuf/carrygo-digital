<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['sometimes', 'string'],
            'opening_points' => ['sometimes', 'integer', 'min:1'],
            'countdown_duration_seconds' => ['sometimes', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'max:2048'],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'publish' => ['nullable', 'boolean'],
        ];
    }
}
