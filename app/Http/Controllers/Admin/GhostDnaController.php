<?php

namespace App\Http\Controllers\Admin;

use App\GhostDna;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class GhostDnaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dna = GhostDna::orderBy('pair')->get();
        $pairs = $dna->groupBy('pair');

        return view('ghostDna.index', compact('pairs'));
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
            'sequence' => 'required|check_dna_sequence|size:6|min:6|unique:ghost_dnas,sequence',
        ]);

        $dna = new GhostDna;

        $pair = DB::table('ghost_dnas')->select('pair')->orderBy('pair', 'desc')->first()->pair + 1;

        $dna->fill($request->all());
        $dna->pair = $pair;
        $dna->save();

        $flip = new GhostDna;
        $flip->sequence = $this->generateMatchingSequence($dna->sequence);
        $flip->pair = $pair;
        $flip->save();

        return redirect()->route('admin.ghostDna.index')->with('alert', ['type' => 'success', 'message' => 'DNA pair added!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $pairId
     * @return \Illuminate\Http\Response
     */
    public function destroy($pairId)
    {
        $dna = GhostDna::with('teams')->where('pair', '=', $pairId)->get();
        $delete = true;
        foreach ($dna as $d) {
            if (! $d->teams->isEmpty()) {
                $delete = false;
            }
        }
        if ($delete) {
            foreach ($dna as $d) {
                $d->delete();
            }
            $alert = ['type' => 'success', 'message' => 'DNA pair deleted!'];
        } else {
            $alert = ['type' => 'warning', 'message' => 'Cannot delete this pair. One of the sequences was found in a previous game. Deleting it would affect the team\'s score'];
        }

        return redirect()->route('admin.ghostDna.index')->with('alert', $alert);
    }

    private function generateMatchingSequence($sequence)
    {
        $match = function ($char) {
            switch ($char) {
                case 'g':
                    return 'h';
                case 't':
                    return 's';
                case 'h':
                    return 'g';
                case 's':
                    return 't';
            }
        };

        return implode('', array_map($match, str_split($sequence)));
    }
}
