<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MinigameImage;
use Illuminate\Http\Request;

class MinigameImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $minigameImages = MinigameImage::orderBy('year')->get();

        return view('minigameImage.index', compact('minigameImages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $minigameImage = new MinigameImage;

        return view('minigameImage.create', compact('minigameImage'));
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
            'year' => 'required',
        ]);

        $minigameImage = new MinigameImage;
        $minigameImage->fill($request->all());
        // Add Image
        if ($request->file('new_image_file')) {
            $this->validate($request, [
                'new_image_file' => 'max:1024|mimetypes:image/jpeg,image/png,image/svg+xml',
            ]);
            $path = $request->file('new_image_file')->store('minigame_images', 'public');
            $minigameImage->src = $path;
        }

        $minigameImage->save();

        return redirect()
                ->route('admin.minigameImage.index')
                ->with('alert', ['message' => $minigameImage->name.' saved!', 'type' => 'success']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $minigameImage = MinigameImage::findOrFail($id);

        return view('minigameImage.edit', compact('minigameImage'));
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
            'year' => 'required',
        ]);

        $minigameImage = MinigameImage::findOrFail($id);
        $minigameImage->fill($request->all());
        // Update Image
        if ($request->file('new_image_file')) {
            $this->validate($request, [
                'new_image_file' => 'max:1024|mimetypes:image/jpeg,image/png,image/svg+xml',
            ]);
            $path = $request->file('new_image_file')->store('minigame_images', 'public');
            $minigameImage->deleteImage();
            $minigameImage->src = $path;
        }
        $minigameImage->save();

        return redirect()
                ->route('admin.minigameImage.index')
                ->with('alert', ['message' => $minigameImage->name.' updated!', 'type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $minigameImage = MinigameImage::with('quests')->findOrFail($id);
        if ($minigameImage->quests->isEmpty()) {
            $minigameImage->deleteImage();
            $minigameImage->delete();
            $alert = ['type' => 'success', 'message' => $minigameImage->name.' deleted!'];
        } else {
            $alert = ['type' => 'danger', 'message' => $minigameImage->name.' cannot be deleted. It is attached to past games.'];
        }

        return redirect()
                ->route('admin.minigameImage.index')
                ->with('alert', $alert);
    }
}
