<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{

    public function __construct()
    {

    }

    /*
     * Updates the homepage alert
     */
    public function updateHomePageAlert(Request $request){
        DB::table('globals')->where('key','homepage')->update(['message' => $request->input('homepage-alert')]);
        return redirect()->back();
    }

}
