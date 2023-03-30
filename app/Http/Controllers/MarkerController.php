<?php

namespace App\Http\Controllers;

use App\Http\Requests\Marker\CreateRequest;
use App\Http\Requests\Marker\EditRequest;
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
        if ($request->has('southwest') && $request->has('northeast')) {
            $southwest = $request->southwest;
            $northeast = $request->northeast;
            $markers = Marker::whereBetween('lat', [$southwest['lat'], $northeast['lat']])
                ->whereBetween('lng', [$southwest['lng'], $northeast['lng']])->with('texts', 'photos')->where('user_id', $request->user()->id)->get();
        } else {
            $markers = [];
        }
        return Inertia::render('Sites/Map', [
            'markers' => $markers,
        ]);
    }


    public function edit(EditRequest $request, Marker $marker) {
        $request->commit();
        return Redirect::route('sites');
    }


    public function visitor(Request $request) {
        if ($request->has('southwest') && $request->has('northeast')) {
            $southwest = $request->southwest;
            $northeast = $request->northeast;
            $markers = Marker::whereBetween('lat', [$southwest['lat'], $northeast['lat']])
                ->whereBetween('lng', [$southwest['lng'], $northeast['lng']])->select('lat', 'lng', 'id')->get();
        } else {
            $markers = [];
        }
        return Inertia::render('Main', [
            'markers' => $markers,
        ]);
    }

    public function displayMarker(Marker $marker) {
        $photos = $marker->photos()->select('url')->get();
        $texts = $marker->texts()->select('provider', 'text')->get();
        return back()->with('message', [
                'type' => $marker->type,
                'photos' => $photos,
                'texts' => $texts
            ]
        );
    }
}
