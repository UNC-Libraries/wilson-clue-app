<?php

namespace App\Http\Controllers\Admin;

use App\Evidence;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvidenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index(): Factory|View
    {
        $evidence = Evidence::get();

        return view('evidence.index', compact('evidence'));
    }

    /**
     * Return a json list of evidence
     *
     * @param Request $request
     * @return Factory|View|JsonResponse
     */
    public function getEvidence(Request $request): Factory|View|JsonResponse
    {
        $evidence = Evidence::query();

        $gameId = $request->input('game_id');
        $exclude_evidence = $request->input('exclude_evidence');
        $view = $request->input('view') ? $request->input('view') : null;

        if (! empty($gameId)) {
            $evidence->whereHas('games', function ($query) use ($gameId) {
                $query->where('game_id', '=', $gameId);
            });
        }

        if (! empty($exclude_evidence)) {
            $evidence->whereNotIn('id', explode(',', $exclude_evidence));
        }

        if ($view) {
            return view($view, ['questions' => $evidence->get()]);
        } else {
            return response()->json($evidence->get());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create(): Factory|View
    {
        $evidence = new Evidence;

        return view('evidence.create', compact('evidence'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate
        $request->validate([
            'title' => 'required',
        ]);

        // Load and fill question
        $evidence = new Evidence;
        $evidence->fill($request->all());
        // Add Image
        if ($request->file('new_image_file')) {
            $request->validate([
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
     * @return Factory|View
     */
    public function edit($id): Factory|View
    {
        $evidence = Evidence::findOrFail($id);

        return view('evidence.edit', compact('evidence'));
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
        // Validate
        $request->validate([
            'title' => 'required',
        ]);
        $imageType = $request->get('image_type');

        // Load and fill question
        $evidence = Evidence::findOrFail($id);
        $evidence->fill($request->all());
        // Update Image
        if ($request->file('new_image_file')) {
            $request->validate([
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
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
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
