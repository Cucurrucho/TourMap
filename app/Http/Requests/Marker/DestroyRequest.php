<?php

namespace App\Http\Requests\Marker;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRequest extends FormRequest {
    private $marker;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        $this->marker = $this->route('marker');
        return auth()->check() && auth()->user()->id == $this->marker->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array {
        return [
            //
        ];
    }

    public function commit() {
        return $this->marker->delete();
    }
}
