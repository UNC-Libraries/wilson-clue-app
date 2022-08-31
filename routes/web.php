<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\UiController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/***************************
 * Force HTTPS
 */
if (env('APP_ENV') != 'local') {
    URL::forceScheme('https');
}

/***************************
 * Website routes...
 */
Route::get('/', [WebController::class, 'index'])->name('web.index');
Route::get('archive/{id}', [WebController::class, 'archive'])->where('id', '[0-9]+')->name('web.archive');
Route::get('/game-over', function () {
    return view('errors.game_over');
})->name('gameover');

/***************************
 * Game UI routes...
 */
Route::middleware('activeGame')->group(function () {

    /***************************
     * Registration routes...
    */
    Route::get('enlist', [RegistrationController::class, 'index'])->name('enlist.index');
    Route::post('enlist', [RegistrationController::class, 'enlist'])->name('enlist.submit');

    /***************************
     * Requires authentication routes...
     */
    Route::middleware('auth:player', 'player')->group(function () {
        Route::middleware('validTeam')->group(function () {
            Route::get('start', [UiController::class, 'index'])->name('ui.index');
            // These require and active game
            Route::middleware('inProgressGame')->group(function () {
                Route::prefix('game/')->group(function () {
                    Route::get('quest/{id}', [UiController::class, 'quest'])->name('ui.quest');
                    Route::get('evidence', [UiController::class, 'evidence'])->name('ui.evidence');
                    Route::get('geographic-investigation', [UiController::class, 'geographicInvestigation'])->name('ui.geographicInvestigation');
                    Route::post('indictment', [UiController::class, 'setIndictment'])->name('ui.set.indictment');
                    Route::get('dna', [UiController::class, 'dna'])->name('ui.dna');
                });
                // Ajax routes
                Route::post('attempt/question/{id}', [UiController::class, 'attemptQuestion'])->name('ui.attempt.question');
                Route::post('attempt/dna', [UiController::class, 'attemptDna'])->name('ui.attempt.dna');
                Route::post('attempt/minigame/{id}', [UiController::class, 'attemptMinigame'])->name('ui.attempt.minigame');
                Route::post('set/evidence', [UiController::class, 'setEvidence'])->name('ui.set.evidence');
                Route::post('status/quest/{id}', [UiController::class, 'questStatus'])->name('ui.status.quest');
            });
            Route::prefix('game/')->group(function () {
                Route::get('indictment', [UiController::class, 'indictment'])->name('ui.indictment');
                Route::get('info', [UiController::class, 'info'])->name('ui.info');
                Route::get('map', [UiController::class, 'map'])->name('ui.map');
            });
            // Additional Ajax routes
            Route::post('alert', [UiController::class, 'alert'])->name('ui.alert');
            Route::post('seen/{id}', [UiController::class, 'seen'])->name('ui.alert.seen');
        });

        /***************************
         * Team Management routes...
         */

        Route::get('team', [RegistrationController::class, 'teamManagement'])->name('enlist.teamManagement');
        Route::post('team', [RegistrationController::class, 'updateTeam'])->name('enlist.updateTeam');
        Route::post('team/add-player', [RegistrationController::class, 'addPlayer'])->name('enlist.updateTeam.addPlayer');
        Route::post('remove-player/{playerId}', [RegistrationController::class, 'removePlayer'])->name('enlist.updateTeam.removePlayer');
    });
});

/***************************
 * Authentication routes...
 */
// ADMIN
Route::get('login', [Auth\AdminLoginController::class, 'showLoginForm'])->name('admin.login.form');
Route::post('login', [Auth\AdminLoginController::class, 'login'])->name('admin.login');
Route::get('logout', [Auth\AdminLoginController::class, 'logout'])->name('admin.logout');
// PLAYER
Route::get('go', [Auth\LoginController::class, 'showLoginForm'])->name('player.login.form');
Route::post('go', [Auth\LoginController::class, 'login'])->name('player.login');
Route::get('leave', [Auth\LoginController::class, 'logout'])->name('player.logout');

/***************************
 * Admin routes...
 */
Route::middleware('auth:admin', 'admin')->group(function () {

    // Test game route
    Route::get('/test-game', [Admin\GameController::class, 'overrideInProgress']);

    Route::get('/admin', [Admin\AdminController::class, 'index'])->name('admin');
    Route::prefix('admin')->group(function () {

        // Trash
        Route::get('trash', [Admin\AdminController::class, 'trash'])->name('admin.trash');
        Route::post('restore/{id}', [Admin\AdminController::class, 'restore'])->where('id', '[0-9]+')->name('admin.restore');
        Route::delete('trash/{id}', [Admin\AdminController::class, 'delete'])->where('id', '[0-9]+')->name('admin.delete');

        // Site Messages
        Route::get('site-messages', [Admin\AdminController::class, 'siteMessages'])->name('admin.siteMessages');
        Route::post('site-messages/{key}', [Admin\AdminController::class, 'updateSiteMessage'])->name('admin.siteMessages.update');

        //AGENT index, create, show store edit update destroy
        Route::resource('agent', Admin\AgentController::class, [
            'names' => [
                'index' => 'admin.agent.index',
                'create' => 'admin.agent.create',
                'store' => 'admin.agent.store',
                'edit' => 'admin.agent.edit',
                'update' => 'admin.agent.update',
                'destroy' => 'admin.agent.destroy',
            ],
        ]);

        //EVIDENCE
        Route::resource('evidence', Admin\EvidenceController::class, [
            'names' => [
                'index' => 'admin.evidence.index',
                'create' => 'admin.evidence.create',
                'store' => 'admin.evidence.store',
                'edit' => 'admin.evidence.edit',
                'update' => 'admin.evidence.update',
                'destroy' => 'admin.evidence.destroy',
            ],
        ]);

        Route::prefix('game/{id}')->group(function () {

            //Activate
            Route::get('/activate', [Admin\GameController::class, 'activate'])->where('id', '[0-9]+')->name('admin.game.activate');
            Route::get('/deactivate', [Admin\GameController::class, 'deactivate'])->where('id', '[0-9]+')->name('admin.game.deactivate');
            //Archive
            Route::get('/archive', [Admin\GameController::class, 'editArchive'])->where('id', '[0-9]+')->name('admin.game.edit.archive');
            //Evidence Room
            Route::get('/evidence', [Admin\GameController::class, 'editEvidence'])->where('id', '[0-9]+')->name('admin.game.edit.evidence');
            Route::post('/import-evidence-room', [Admin\GameController::class, 'importEvidenceRoom'])->where('id', '[0-9]+')->name('admin.game.import-evidence-room');
            //Teams
            Route::get('teams', [Admin\GameController::class, 'teams'])->where('id', '[0-9]+')->name('admin.game.teams');
            Route::post('add-team', [Admin\GameController::class, 'addTeam'])->where('id', '[0-9]+')->name('admin.game.addTeam');
            //Judgement
            Route::get('judgement', [Admin\GameController::class, 'judgement'])->where('id', '[0-9]+')->name('admin.game.judgement');
            Route::post('/judge/{questId}/{questionId}/{teamId}', [Admin\GameController::class, 'judgeAnswers'])
                ->where('id', '[0-9]+')
                ->where('questId', '[0-9]+')
                ->where('questionId', '[0-9]+')
                ->where('teamId', '[0-9]+')
                ->name('admin.game.judgeAnswers');
            // Scoring
            Route::post('/bonus-points', [Admin\GameController::class, 'bonusPoints'])->where('id', '[0-9]+')->name('admin.game.bonus');
            Route::get('/score/{includeWaitlist?}', [Admin\GameController::class, 'score'])
                ->where('id', '[0-9]+')
                ->name('admin.game.score');
            // Player check-in
            Route::get('/check-in', [Admin\GameController::class, 'checkIn'])
                ->where('id', '[0-9]+')
                ->name('admin.game.checkin');
            Route::post('/player/check-in/{playerId?}', [Admin\GameController::class, 'checkInPlayer'])->where('id', '[0-9]+')->name('admin.game.checkin.player');

            // GLADOS
            Route::get('glados/viewing', [Admin\GladosController::class, 'viewing'])->name('admin.game.glados.viewing');
            Route::get('glados/status', [Admin\GladosController::class, 'status'])->name('admin.game.glados.status');

            // ALERTS
            Route::resource('alert', Admin\AlertController::class, [ 'names' => [
                'store' => 'admin.game.alert.store',
                'destroy' => 'admin.game.alert.destroy',
            ]]);
        });
        Route::resource('game', Admin\GameController::class, [
            'names' => [
                'index' => 'admin.game.index',
                'create' => 'admin.game.create',
                'store' => 'admin.game.store',
                'show' => 'admin.game.show',
                'edit' => 'admin.game.edit',
                'update' => 'admin.game.update',
                'destroy' => 'admin.game.destroy',
            ],
        ]);

        //LOCATION
        Route::resource('location', Admin\LocationController::class, [
            'names' => [
                'index' => 'admin.location.index',
                'create' => 'admin.location.create',
                'store' => 'admin.location.store',
                'show' => 'admin.location.show',
                'edit' => 'admin.location.edit',
                'update' => 'admin.location.update',
                'destroy' => 'admin.location.destroy',
            ],
        ]);

        //MINIGAME IMAGES
        Route::resource('minigame-image', Admin\MinigameImageController::class, [
            'names' => [
                'index' => 'admin.minigameImage.index',
                'create' => 'admin.minigameImage.create',
                'store' => 'admin.minigameImage.store',
                'edit' => 'admin.minigameImage.edit',
                'update' => 'admin.minigameImage.update',
                'destroy' => 'admin.minigameImage.destroy',
            ],
        ]);

        //Ghost Dna
        Route::resource('ghost-dna', Admin\GhostDnaController::class, [
            'names' => [
                'index' => 'admin.ghostDna.index',
                'store' => 'admin.ghostDna.store',
                'destroy' => 'admin.ghostDna.destroy',
            ],]);

        //PLAYERS
        Route::resource('player', Admin\PlayerController::class, [
            'names' => [
                'index' => 'admin.player.index',
                'show' => 'admin.player.show',
                'edit' => 'admin.player.edit',
                'update' => 'admin.player.update',
                'destroy' => 'admin.player.destroy',
            ],
        ]);

        //QUEST
        Route::post('quest/{id}/add/{questionId}', [Admin\QuestController::class, 'addQuestion'])->where('id', '[0-9]+')->where('questionId', '[0-9]+');
        Route::post('quest/{id}/remove/{questionId}', [Admin\QuestController::class, 'removeQuestion'])->where('id', '[0-9]+')->where('questionId', '[0-9]+');
        Route::post('quest/{id}/reorder/{questionId}', [Admin\QuestController::class, 'reorderQuestions'])->where('id', '[0-9]+')->where('questionId', '[0-9]+');
        Route::resource('game.quest', Admin\QuestController::class, [
            'names' => [
                'edit' => 'admin.game.quest.edit',
                'update' => 'admin.game.quest.update',
            ],
        ]);

        //QUESTION
        Route::delete('answer/{id}', [Admin\QuestionController::class, 'destroyAnswer'])->where('id', '[0-9]+')->name('admin.destroy.answer');
        Route::get('answer/new', [Admin\QuestionController::class, 'newAnswer'])->name('admin.new.answer');
        Route::resource('question', Admin\QuestionController::class, [
            'names' => [
                'index' => 'admin.question.index',
                'create' => 'admin.question.create',
                'store' => 'admin.question.store',
                'edit' => 'admin.question.edit',
                'update' => 'admin.question.update',
                'destroy' => 'admin.question.destroy',
            ],
        ]);

        //SUSPECT
        Route::resource('suspect', Admin\SuspectController::class, [
            'names' => [
                'index' => 'admin.suspect.index',
                'edit' => 'admin.suspect.edit',
                'update' => 'admin.suspect.update',
            ],
        ]);

        // TEAM
        Route::post('team/{id}/waitlist', [Admin\TeamController::class, 'toggleWaitlist'])->where('id', '[0-9]+')->name('admin.team.waitlist');
        Route::delete('team/{id}/removePlayer/{playerId}', [Admin\TeamController::class, 'removePlayer'])->where('id', '[0-9]+')->where('playerId', '[0-9]+')->name('admin.team.removePlayer');
        Route::post('team/{id}/addPlayer', [Admin\TeamController::class, 'addPlayer'])->where('id', '[0-9]+')->name('admin.team.addPlayer');
        Route::resource('team', Admin\TeamController::class, [
            'names' => [
                'edit' => 'admin.team.edit',
                'update' => 'admin.team.update',
                'destroy' => 'admin.team.destroy',
            ],
        ]);

        // MISC
        Route::get('case-file-item-form', function () {
            return view('game._case_file_item_form');
        })->name('admin.casefileItemForm');

        Route::put('update-homepage-alert', [Admin\SiteController::class, 'updateHomepageAlert'])->name('admin.site.updateHomepageAlert');
    });
});
