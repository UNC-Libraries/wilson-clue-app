<?php

namespace App\Http\Controllers\Admin;

use App\Evidence;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvidenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $evidence = Evidence::get();

        return view('evidence.index', compact('evidence'));
    }

    /**
     * Return a json list of evidence
     *
     * @return \Illuminate\Http\Response
     */
    public function getEvidence(Request $request)
    {
        $evidence = Evidence::select();

        $gameId = $request->input('game_id');
        $exclude_evidence = $request->input('exclude_evidence');
        $view = $request->input('view') ? $request->input('view') : null;

        if (! empty($gameId)) {
            $evidence->whereHas('games', function ($query) use ($game) {
                $query->where('game_id', '=', $game);
            });
        }

        if (! empty($exclude_evidence)) {
            $evidence->whereNotIn('id', explode(',', $exclude_evidence));
        }

        if ($view) {
            return view($view, compact('questions'));
        } else {
            return response()->json($evidence);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $evidence = new Evidence;

        return view('evidence.create', compact('evidence'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //Validate
        $this->validate($request, [
            'title' => 'required',
        ]);

        // Load and fill question
        $evidence = new Evidence;
        $evidence->fill($request->all());
        // Add Image
        if ($request->file('new_image_file')) {
            $this->validate($request, [
                'new_image_file' => 'max:1024|mimetypes:image/jpeg,image/png,image/svg+xml',
            ]);
            $path = $request->file('new_image_file')->store('evidence', 'public');
            $evidence->src = $path;
        }

        // Save evidence
        $evidence->save();

        return redirect()->route('admin.evidence.index')->with('alert', ['message' => $evidence->title.' created!', 'type' => 'success']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $evidence = Evidence::findOrFail($id);

        return view('evidence.edit', compact('evidence'));
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

        //Validate
        $this->validate($request, [
            'title' => 'required',
        ]);
        $imageType = $request->get('image_type');

        // Load and fill question
        $evidence = Evidence::findOrFail($id);
        $evidence->fill($request->all());
        $evidence->fill($request->all());
        // Update Image
        if ($request->file('new_image_file')) {
            $this->validate($request, [
                'new_image_file' => 'max:1024|mimetypes:image/jpeg,image/png,image/svg+xml',
            ]);
            $path = $request->file('new_image_file')->store('evidence', 'public');
            $evidence->deleteImage();
            $evidence->src = $path;
        }

        // Save evidence
        $evidence->save();

        return redirect()->route('admin.evidence.index')->with('alert', ['message' => $evidence->title.' updated!', 'type' => 'success']);
    }

    /**
     * Destroy the specified resource
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $evidence = Evidence::with('games')->findOrFail($id);
        if ($evidence->games->isEmpty()) {
            $evidence->deleteImage();
            $evidence->delete();
            $alert = ['type' => 'success', 'message' => $evidence->title.' deleted!'];
        } else {
            $alert = ['type' => 'danger', 'message' => $evidence->title.' cannot be deleted. It is attached to past games.'];
        }

        return redirect()->route('admin.evidence.index')->with('alert', $alert);
    }
}
