<?php

namespace App\Http\Controllers\Admin;

use App\Alert;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function store(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $alert = new Alert(['message' => $request->get('message')]);
        $alert->game()->associate($id);
        $alert->save();

        return redirect()->route('admin.game.show', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @param  int  $alertId
     * @return RedirectResponse
     */
    public function destroy(int $id, int $alertId): RedirectResponse
    {
        $agent = Alert::findOrFail($alertId);
        $agent->delete();

        return redirect()->route('admin.game.show', $id);
    }
}
