<?php

namespace App\Http\Controllers\Admin;

use App\Game;
use App\Http\Controllers\Controller;
use App\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locations = Location::get();

        return view('location.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $location = new Location;
        $mapSections = config('map_sections');

        return view('location.create', compact('location', 'mapSections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'floor' => 'required|integer',
            'map_section' => 'required',
        ]);

        $location = new Location;

        $location->fill($request->all());

        $location->save();

        return redirect()->route('admin.location.index')->with('alert', ['message' => $location->name.'  created', 'type' => 'success']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $location = Location::findOrFail($id);
        $mapSections = config('map_sections');

        return view('location.edit', compact('location', 'mapSections'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'floor' => 'required|integer',
            'map_section' => 'required',
        ]);

        $location = Location::findOrFail($id);

        $location->fill($request->all());

        $location->save();

        return redirect()->route('admin.location.index')->with('alert', ['message' => $location->name.'  updated', 'type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $location = Location::with('quests')->findOrFail($id);
        $games = Game::where('evidence_location_id', '=', $id)
            ->orWhere('geographic_investigation_location_id', '=', $id)->get();
        if ($location->quests->isEmpty() && $games->isEmpty()) {
            $location->delete();
            $alert = ['type' => 'success', 'message' => $location->name.' deleted!'];
        } else {
            $alert = ['type' => 'danger', 'message' => $location->name.' cannot be deleted. It is was used in a previous game.'];
        }

        return redirect()->route('admin.location.index')->with('alert', $alert);
    }
}
