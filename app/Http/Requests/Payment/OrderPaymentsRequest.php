<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderPaymentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('order')->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(PaymentStatus::cases())],
            'payment_method' => ['sometimes', 'string', Rule::in(PaymentMethod::cases())],
        ];
    }
}
