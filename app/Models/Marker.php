<?php

namespace App\Models;

use App\Models\MarkerResources\MarkerPhoto;
use App\Models\MarkerResources\MarkerText;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marker extends Model {
    use HasFactory;

    public static function boot() {
        parent::boot();

        static::deleting(function($marker) { // before delete() method call this
            $marker->photos()->delete();
            $marker->texts()->delete();
            // do the rest of the cleanup...
        });
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function texts() {
        return $this->hasMany(MarkerText::class);
    }

    public function photos() {
        return $this->hasMany(MarkerPhoto::class);
    }
}
