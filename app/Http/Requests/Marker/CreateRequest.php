<?php

namespace App\Http\Requests\Marker;

use App\Models\Marker;
use App\Models\MarkerResources\MarkerPhoto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CreateRequest extends FormRequest {
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
            'type' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'name' => 'required|string',
            'text' => 'required|string'
        ];
    }

    public function commit() {
        $marker = new Marker;
        $marker->type = $this->type;
        $marker->lat = $this->lat;
        $marker->lng = $this->lng;
        $marker->name = $this->name;
        $marker->text = $this->text;
        $this->user()->markers()->save($marker);
        foreach ($this->images as $image){
            if (!is_array($image)){
                $markerImage = new MarkerPhoto;
                $hash = $image->hashName();
                $image = Image::make($image);
                $mime = $image->mime();
                $path = "public/markers/$marker->id/$hash";
                if ($mime != 'image/png') {
                    $image->encode('jpg', 70);
                } else {
                    $image->encode('png');
                }
                $markerImage->url = str_replace('public','storage',$path);
                Storage::put($path, $image);
                $marker->photos()->save($markerImage);
            }
        }
        $marker->save();
        return [
            'lat' => $marker->lat,
            'lng' => $marker->lng
        ];

    }
}
