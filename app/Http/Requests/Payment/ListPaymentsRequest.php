<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;

class ListPaymentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(PaymentStatus::cases())],
            'payment_method' => ['sometimes', 'string', Rule::in(PaymentMethod::cases())],
        ];
    }
}
