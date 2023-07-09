<?php

namespace App\Http\Requests\Marker;

use App\Models\MarkerResources\MarkerPhoto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class EditRequest extends FormRequest {
    protected $marker;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        $this->marker = $this->route('marker');
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
        $this->marker->type = $this->type;
        $this->marker->name = $this->name;
        $this->marker->text = $this->text;
        $this->marker->save();

        $photosToDelete = $this->marker->photos;
        foreach ($this->images as $image){
            if (is_a($image, UploadedFile::class)){
                $markerImage = new MarkerPhoto;
                $hash = $image->hashName();
                $image = Image::make($image);
                $mime = $image->mime();
                $path = "public/markers/" . $this->marker->id . "/$hash";
                if ($mime != 'image/png') {
                    $image->encode('jpg', 70);
                } else {
                    $image->encode('png');
                }
                $markerImage->url = str_replace('public','storage',$path);
                Storage::put($path, $image);
                $this->marker->photos()->save($markerImage);
            } else {
                $photosToDelete = $photosToDelete->reject(function ($markerPhoto) use ($image){
                    return $markerPhoto->id == $image['id'];
                });
            }
        }
        $photosToDelete->each(function ($photo){
            $photo->delete();
        });
        return [
            'lat' => $this->marker->lat,
            'lng' => $this->marker->lng
        ];
    }
}
