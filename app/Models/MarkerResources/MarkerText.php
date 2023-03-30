<?php

namespace App\Models\MarkerResources;

use App\Models\Marker;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkerText extends Model {
    use HasFactory;
    public function marker() {
        return $this->belongsTo(Marker::class);
    }
}
