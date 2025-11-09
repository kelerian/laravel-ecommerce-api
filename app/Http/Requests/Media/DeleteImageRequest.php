<?php

namespace App\Http\Requests\Media;

use App\Models\Media\News;
use App\Traits\FailedValidationForDelete;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DeleteImageRequest extends FormRequest
{
    use FailedValidationForDelete;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $modelId = $this->id_model;
        $modelClass = $this->resolveModelClass($this->model_type);
        $modelObject = $modelClass::where('id', $modelId)->firstOrFail();
        return Gate::allows('delete-images', $modelObject);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'model_type' => 'required|string|in:news,products,users',
            'id_model' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $modelClass = $this->resolveModelClass($this->model_type);
                    if (!$modelClass::where('id', $value)->exists()) {
                        $fail("The selected {$this->model_type} does not exist.");
                    }
                }
            ],
            'image_id' => 'array',
            'image_id.*' => 'required|integer|exists:media,id',
        ];
    }


    protected function resolveModelClass(string $type): string
    {
        return match ($type) {
            'news' => \App\Models\Media\News::class,
            'products' => \App\Models\Products\Product::class,
            'users' => \App\Models\Users\User::class,
        };
    }





}
