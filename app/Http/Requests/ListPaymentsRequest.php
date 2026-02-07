<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListPaymentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:pending,successful,failed'],
            'payment_method' => ['sometimes', 'string', 'in:credit_card,paypal,stripe'],
        ];
    }
}
