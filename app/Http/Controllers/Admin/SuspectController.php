<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Suspect;

class SuspectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suspects = Suspect::get();
        return view('suspect.index',compact('suspects'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $suspect = Suspect::findOrFail($id);
        return view('suspect.edit',compact('suspect'));
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
        $suspect = Suspect::findOrFail($id);

        foreach($suspect->getAttributes() as $key => $value){

            if(isset($request->{$key}) && $value !== $request->{$key}){
                $suspect->{$key} = $request->{$key};
            }
        }

        $suspect->save();

        return redirect()->route('admin.suspect.index')->with(['message'=>$suspect->name.' updated']);
    }

}
