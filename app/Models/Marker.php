<?php

namespace App\Models;

use App\Models\MarkerResources\MarkerPhoto;
use App\Models\MarkerResources\MarkerText;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marker extends Model {
    use HasFactory;

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
