<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Alert;

class AlertController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        $this->validate($request,[
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
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $alertId)
    {
        $agent = Alert::findOrFail($alertId);
        $agent->delete();
        return redirect()->route('admin.game.show', $id);

    }
}
