<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validasi pembuatan integrasi website (dipakai dashboard web & REST API). */
class StoreIntegrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'domain' => 'required|string|max:255',
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'greeting_title' => 'nullable|string|max:100',
            'greeting_subtitle' => 'nullable|string|max:255',
            'agent_scope' => 'nullable|in:all,selected',
            'agent_ids' => 'nullable|array',
            'agent_ids.*' => 'integer',
        ];
    }
}
