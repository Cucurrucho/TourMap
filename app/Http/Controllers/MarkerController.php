<?php

namespace App\Http\Controllers;

use App\Http\Requests\Marker\CreateRequest;
use App\Http\Requests\Marker\DestroyRequest;
use App\Http\Requests\Marker\EditRequest;
use App\Http\Requests\Marker\UpdatePositionRequest;
use App\Models\Marker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;


class MarkerController extends Controller {
    public function create(CreateRequest $request) {
        $request->commit();
        return Redirect::route('sites');
    }

    public function get(Request $request) {
        $markers = Marker::with( 'photos')->where('user_id', $request->user()->id)->get();
        return Inertia::render('Sites/Map', [
            'markers' => $markers,
        ]);
    }


    public function edit(EditRequest $request, Marker $marker) {
        $request->commit();
        return Redirect::route('sites');
    }


    public function visitor(Request $request) {
        return Inertia::render('Main');
    }

    public function displayMarker(Marker $marker) {
        $photos = $marker->photos()->select('url')->get();
        return back()->with('message', [
                'type' => $marker->type,
                'photos' => $photos,
                'name' => $marker->name,
                'creator' => $marker->user->name
            ]
        );
    }

    public function getMarkers(Request $request) {
        $distance = 0.02;
        $position = $request->position;
        $sites = Marker::whereBetween('lat', [$position['lat'] - $distance, $position['lat'] + $distance])->whereBetween('lng', [$position['lng'] - $distance, $position['lng'] + $distance])->get();
        return back()->with('message', [
            'sites' => $sites
        ]);
    }


    public function updateMarkerPositions(UpdatePositionRequest $request) {
        $request->commit();
        return back();
    }

    public function destroy(DestroyRequest $request, Marker $marker) {
        $request->commit();
        return back();
    }
}


