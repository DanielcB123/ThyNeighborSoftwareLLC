<?php

declare(strict_types=1);

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class UploadOnboardingMaterialsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sessionToken' => ['required', 'string', 'regex:/^[A-Za-z0-9]{64}$/'],
            'materials' => ['required', 'array', 'min:1', 'max:5'],
            'materials.*' => [
                'required',
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,png,jpg,jpeg,webp,txt,zip',
            ],
        ];
    }
}
