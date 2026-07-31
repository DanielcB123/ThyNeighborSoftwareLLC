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
            'sessionToken' => ['required', 'string', 'max:80', 'exists:onboarding_sessions,access_token'],
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
