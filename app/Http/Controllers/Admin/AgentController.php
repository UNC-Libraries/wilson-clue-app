<?php

namespace App\Http\Controllers\Admin;

use App\Agent;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index(): Factory|View
    {
        $agents = Agent::orderBy('last_name')->get();

        return view('agent.index', compact('agents'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create(): Factory|View
    {
        $agent = new Agent;

        return view('agent.create', compact('agent'));
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
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        $agent = new Agent;
        $agent->fill($request->all());

        if ($request->file('new_image_file')) {
            $request->validate([
                'new_image_file' => 'max:1024|mimetypes:image/jpeg,image/png,image/svg+xml',
            ]);
            $path = $request->file('new_image_file')->store('agents', 'public');
            $agent->src = $path;
        }

        $agent->save();

        return redirect()->route('admin.agent.index')->with('alert', ['type' => 'success', 'message' => $agent->title.' '.$agent->last_name.' saved!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Factory|View
     */
    public function edit(int $id): Factory|View
    {
        $agent = Agent::findOrFail($id);

        return view('agent.edit', compact('agent'));
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
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        $agent = Agent::findOrFail($id);

        // Reset for stupid checkboxes
        $agent->retired = false;
        $agent->web_display = false;
        $agent->admin = false;

        $agent->fill($request->all());
        if ($request->file('new_image_file')) {
            $request->validate([
                'new_image_file' => 'max:1024|mimetypes:image/jpeg,image/png,image/svg+xml',
            ]);
            $path = $request->file('new_image_file')->store('agents', 'public');
            $agent->deleteImage();
            $agent->src = $path;
        }

        $agent->save();

        return redirect()->route('admin.agent.index')->with('alert', ['type' => 'success', 'message' => $agent->title.' '.$agent->last_name.' updated!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $agent = Agent::findOrFail($id);
        $agent->deleteImage();
        $agent->delete();

        return redirect()->route('admin.agent.index')->with('alert', ['type' => 'success', 'message' => $agent->title.' '.$agent->last_name.' deleted!']);
    }
}
