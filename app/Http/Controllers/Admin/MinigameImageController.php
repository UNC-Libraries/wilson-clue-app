<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MinigameImage;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MinigameImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index(): Factory|View
    {
        $minigameImages = MinigameImage::orderBy('year')->get();

        return view('minigameImage.index', compact('minigameImages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create(): Factory|View
    {
        $minigameImage = new MinigameImage;

        return view('minigameImage.create', compact('minigameImage'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'year' => 'required',
        ]);

        $minigameImage = new MinigameImage;
        $minigameImage->fill($request->all());
        // Add Image
        if ($request->file('new_image_file')) {
            $request->validate([
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
     * @param int $id
     * @return Factory|View
     */
    public function edit(int $id): Factory|View
    {
        $minigameImage = MinigameImage::findOrFail($id);

        return view('minigameImage.edit', compact('minigameImage'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'year' => 'required',
        ]);

        $minigameImage = MinigameImage::findOrFail($id);
        $minigameImage->fill($request->all());
        // Update Image
        if ($request->file('new_image_file')) {
            $request->validate([
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
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
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
