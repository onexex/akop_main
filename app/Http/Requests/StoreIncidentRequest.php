<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reference' => 'required|string',
            'reporter' => 'required|string',
            'subject' => 'required|string',
            'date_of_report' => 'required|date',
            'designation' => 'required|string',
            'source' => 'required|string',
            'dateAcquired' => 'required|string',
            'mannerAcquired' => 'required|string',
            'evaluation' => 'required|string',
            'informationProper' => 'required|string',
            'analysis' => 'required|string',
            'actions' => 'required|string',
        ];
    }
}
