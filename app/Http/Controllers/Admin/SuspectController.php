<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Suspect;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SuspectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index(): Factory|View
    {
        $suspects = Suspect::get();

        return view('suspect.index', compact('suspects'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Factory|View
     */
    public function edit(int $id): Factory|View
    {
        $suspect = Suspect::findOrFail($id);

        return view('suspect.edit', compact('suspect'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $suspect = Suspect::findOrFail($id);

        foreach ($suspect->getAttributes() as $key => $value) {
            if (isset($request->{$key}) && $value !== $request->{$key}) {
                $suspect->{$key} = $request->{$key};
            }
        }

        $suspect->save();

        return redirect()->route('admin.suspect.index')->with(['message' => $suspect->name.' updated']);
    }
}
