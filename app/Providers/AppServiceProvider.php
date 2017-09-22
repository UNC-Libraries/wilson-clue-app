<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Validation\ClueValidator;
use Validator;
use Route;
use App\Game;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Make the controller and action names available to views
        app('view')->composer('layouts.master', function ($view) {
            $action = app('request')->route()->getAction();
            $controller = class_basename($action['controller']);
            list($controller, $action) = explode('@', $controller);
            $controller = camel_case(str_replace('Controller','',$controller));
            $view->with(compact('controller', 'action'));
        });

        $games = Game::all();
        $assets = config('assets');

        app('view')->composer('layouts.admin', function($view) use ($games, $assets){
            $view->with(compact('games','assets'));
        });
        app('view')->composer('admin.index', function($view) use ($games, $assets){
            $view->with(compact('games','assets'));
        }) ;

        app('view')->composer('layouts.ui', function() {
            $route = Route::current();
            $game = Game::inProgress()->first();
            $user = $user = Auth::guard('player')->user();

            if($route && $game && $user){

                $routeName = $route->getName() == 'ui.quest' ? $route->getName().'--'.$route->parameter('id') : $route->getName();

                $base = DB::table('viewing')->where('player_id', $user->id)->where('game_id', $game->id);
                $view = $base->first();
                if ($view) {
                    $base->update(['route' => $routeName, 'updated_at' => Carbon::now()]);
                } else {
                    DB::table('viewing')->insert([
                        'player_id' => $user->id,
                        'game_id' => $game->id,
                        'route' => $routeName,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        });

        Validator::extend('check_dna_sequence','ClueValidator@checkDnaSequence');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
