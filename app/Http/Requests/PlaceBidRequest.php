<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PlaceBidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'points' => ['required', 'integer', 'min:' . config('points.min_bid_increment')],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $points = (int) $this->input('points');

                if (! $validator->errors()->has('points') && $this->user()->points_balance < $points) {
                    $validator->errors()->add(
                        'points',
                        "You only have {$this->user()->points_balance} points available."
                    );
                }
            },
        ];
    }
}
