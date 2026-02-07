<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ShowPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('payment')->order->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [];
    }
}
