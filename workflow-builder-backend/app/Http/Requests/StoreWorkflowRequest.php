<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'nodes' => ['required', 'array', 'min:1'],
            'nodes.*.id' => ['required', 'string', 'max:120'],
            'nodes.*.type' => ['required', Rule::in(['start', 'log', 'color'])],
            'nodes.*.x' => ['required', 'integer', 'min:0'],
            'nodes.*.y' => ['required', 'integer', 'min:0'],
            'nodes.*.text' => ['nullable', 'string'],
            'nodes.*.color' => ['nullable', 'string', 'max:20'],
            'edges' => ['present', 'array'],
            'edges.*.from' => ['required', 'string', 'max:120'],
            'edges.*.to' => ['required', 'string', 'max:120'],
        ];
    }
}
