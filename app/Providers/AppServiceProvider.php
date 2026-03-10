<?php

namespace App\Providers;

use App\Game;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Parsedown;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Bootstrap any application services.
     *
     * Registers view composers, a custom validator extension, and the API
     * rate limiter. Guard-railed with the SKIP_BOOTERS env flag so that
     * migrate:fresh can run before the DB tables exist.
     */
    public function boot(): void
    {
        // Skip all boot logic when running migrations from scratch so that
        // the view composers and DB queries don't fire before tables exist.
        // Usage: SKIP_BOOTERS=true php artisan migrate
        if (env('SKIP_BOOTERS')) {
            return;
        }

        // Inject the current controller and action names into every view that
        // extends layouts.master so Blade templates can highlight the active
        // navigation item or conditionally render sections.
        app('view')->composer('layouts.master', function ($view) {
            $route = app('request')->route();

            // No route is matched outside a real HTTP dispatch (e.g. when a
            // unit test calls View::make() directly), so provide safe defaults
            // rather than calling getAction() on null.
            if (!$route) {
                $view->with(['controller' => '', 'action' => '']);
                return;
            }

            $routeAction = $route->getAction();

            // Closure routes have no 'controller' key, so fall back to empty
            // strings to avoid an "Undefined array key" error.
            if (empty($routeAction['controller'])) {
                $view->with(['controller' => '', 'action' => '']);
                return;
            }

            // Strip the namespace and 'Controller' suffix so the view receives
            // a short camelCase name, e.g. 'GameController@show' → 'game'.
            $controller = class_basename($routeAction['controller']);
            [$controller, $action] = explode('@', $controller);
            $controller = camel_case(str_replace('Controller', '', $controller));
            $view->with(compact('controller', 'action'));
        });

        // Share all games and the asset manifest with the admin layout.
        // The query runs inside the callback (render time) rather than at
        // boot time so that tests always see freshly seeded data.
        app('view')->composer('layouts.admin', function ($view) {
            $view->with([
                'games'  => Game::all(),
                'assets' => config('assets'),
            ]);
        });

        // The admin index view does not extend layouts.admin, so it needs its
        // own composer to receive the same games and assets data.
        app('view')->composer('admin.index', function ($view) {
            $view->with([
                'games'  => Game::all(),
                'assets' => config('assets'),
            ]);
        });

        // Track which route each authenticated player is currently viewing so
        // the admin dashboard can display a live "who is where" map.
        app('view')->composer('layouts.ui', function () {
            $route = Route::current();
            $game  = Game::inProgress()->first();
            $user  = Auth::guard('player')->user();

            // Only write a viewing record when all three are present: an active
            // game, a logged-in player, and a resolved named route.
            if ($route && $game && $user) {
                // Append the quest ID to the route name so that individual
                // quest pages are distinguishable from one another in the
                // viewing table (e.g. "ui.quest--5").
                $routeName = $route->getName() === 'ui.quest'
                    ? $route->getName() . '--' . $route->parameter('id')
                    : $route->getName();

                $base = DB::table('viewing')
                    ->where('player_id', $user->id)
                    ->where('game_id', $game->id);

                $view = $base->first();
                if ($view) {
                    // Player already has a row — update the route and timestamp.
                    $base->update(['route' => $routeName, 'updated_at' => Carbon::now()]);
                } else {
                    // First time this player has viewed this game — insert a row.
                    DB::table('viewing')->insert([
                        'player_id'  => $user->id,
                        'game_id'    => $game->id,
                        'route'      => $routeName,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        });

        // Register the custom DNA-sequence validator rule used in quest
        // submission forms. Delegates to ClueValidator::checkDnaSequence().
        Validator::extend('check_dna_sequence', 'ClueValidator@checkDnaSequence');

        $this->bootRoute();
    }

    /**
     * Register any application services.
     *
     * Binds Parsedown as a singleton so the same Markdown parser instance
     * is reused across the entire request lifecycle instead of being
     * instantiated on every call.
     */
    public function register(): void
    {
        $this->app->singleton(Parsedown::class);
    }

    /**
     * Configure rate limiters for the application.
     *
     * Authenticated users are throttled by their user ID; unauthenticated
     * requests are throttled by IP address. Both allow 60 requests/minute.
     */
    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
