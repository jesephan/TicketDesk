<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'min:10'],
            'priority' => ['sometimes', 'required', 'in:low,medium,high,critical'],
            'category' => ['sometimes', 'required', 'in:bug,feature,improvement,support'],
            'status' => ['sometimes', 'required', 'in:open,in_progress,resolved,closed'],
            'due_date' => ['nullable', 'date', 'after:today'],
        ];
    }
}
