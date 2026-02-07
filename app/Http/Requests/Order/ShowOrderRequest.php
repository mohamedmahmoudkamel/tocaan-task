<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class ShowOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('order')->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [];
    }
}
