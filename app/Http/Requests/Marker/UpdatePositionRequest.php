<?php

namespace App\Http\Requests\Marker;

use App\Models\Marker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePositionRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array {
        return [
            'markers' => 'array'
        ];
    }

    public function commit() {
        foreach ($this->markers as $id => $newPosition) {
            if ($newPosition !== null) {
                $marker = Marker::find($id);
                $marker->lat = $newPosition['latLng']['lat'];
                $marker->lng = $newPosition['latLng']['lng'];
                $marker->save();
            }
        }
    }
}
