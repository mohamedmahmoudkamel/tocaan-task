<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class DeleteOrderRequest extends FormRequest
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
