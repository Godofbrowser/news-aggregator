<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class FilterArticlesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'query' => 'nullable|string|min:2',
            'category' => 'nullable|string|exists:categories,id',
            'sortBy' => 'nullable|string|in:latest,relevance',
            'source' => 'nullable|string|min:2',
        ];
    }
}
