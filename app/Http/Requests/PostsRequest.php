<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostsRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'title' => ['required', 'min:2'],
            'content' => ['required', 'min:10'],
            'tags' => ['required', 'array', 'exists:tags,id'] // exists : 사용자가 넘긴 tags가 db에 있는지 유효성 검사
        ];
    }
}
