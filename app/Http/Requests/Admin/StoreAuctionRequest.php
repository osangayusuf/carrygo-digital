<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuctionRequest extends FormRequest
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
            'category' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string'],
            'opening_points' => ['required', 'integer', 'min:1'],
            'countdown_duration_seconds' => ['required', 'integer', 'min:1'],
            'image' => ['required', 'image', 'max:2048'],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'publish' => ['nullable', 'boolean'],
        ];
    }
}
