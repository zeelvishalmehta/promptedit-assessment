<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGenerationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       return [
            'prompt' => [
                'required',
                'string',
                'max:4000',
            ],

            'size' => [
                'required',
                Rule::in([
                    '1024x1024',
                    '1536x1024',
                    '1024x1536',
                ]),
            ],

            'quality' => [
                'required',
                Rule::in([
                    'low',
                    'medium',
                    'high',
                ]),
            ],

            'reference_images' => [
                'nullable',
                'array',
                'max:4',
            ],

            'reference_images.*' => [
                'required',
                'url:http,https',
            ],
        ];
    }
}
