<?php

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
Route::get('/', 'WebController@index')->name('web.index');
Route::get('archive/{id}', 'WebController@archive')->where('id', '[0-9]+')->name('web.archive');
Route::get('/game-over', function () {
    return view('errors.game_over');
})->name('gameover');

/***************************
 * Game UI routes...
 */
Route::group(['middleware' => ['activeGame']], function () {

    /***************************
     * Registration routes...
    */
    Route::get('enlist', 'RegistrationController@index')->name('enlist.index');
    Route::post('enlist', 'RegistrationController@enlist')->name('enlist.submit');

    /***************************
     * Requires authentication routes...
     */
    Route::group(['middleware' => ['auth:player', 'player']], function () {
        Route::group(['middleware' => ['validTeam']], function () {
            Route::get('start', 'UiController@index')->name('ui.index');
            // These require and active game
            Route::group(['middleware' => ['inProgressGame']], function () {
                Route::group(['prefix' => 'game/'], function () {
                    Route::get('quest/{id}', 'UiController@quest')->name('ui.quest');
                    Route::get('evidence', 'UiController@evidence')->name('ui.evidence');
                    Route::get('geographic-investigation', 'UiController@geographicInvestigation')->name('ui.geographicInvestigation');
                    Route::post('indictment', 'UiController@setIndictment')->name('ui.set.indictment');
                    Route::get('dna', 'UiController@dna')->name('ui.dna');
                });
                // Ajax routes
                Route::post('attempt/question/{id}', 'UiController@attemptQuestion')->name('ui.attempt.question');
                Route::post('attempt/dna', 'UiController@attemptDna')->name('ui.attempt.dna');
                Route::post('attempt/minigame/{id}', 'UiController@attemptMinigame')->name('ui.attempt.minigame');
                Route::post('set/evidence', 'UiController@setEvidence')->name('ui.set.evidence');
                Route::post('status/quest/{id}', 'UiController@questStatus')->name('ui.status.quest');
            });
            Route::group(['prefix' => 'game/'], function () {
                Route::get('indictment', 'UiController@indictment')->name('ui.indictment');
                Route::get('info', 'UiController@info')->name('ui.info');
                Route::get('map', 'UiController@map')->name('ui.map');
            });
            // Additional Ajax routes
            Route::post('alert', 'UiController@alert')->name('ui.alert');
            Route::post('seen/{id}', 'UiController@seen')->name('ui.alert.seen');
        });

        /***************************
         * Team Management routes...
         */

        Route::get('team', 'RegistrationController@teamManagement')->name('enlist.teamManagement');
        Route::post('team', 'RegistrationController@updateTeam')->name('enlist.updateTeam');
        Route::post('team/add-player', 'RegistrationController@addPlayer')->name('enlist.updateTeam.addPlayer');
        Route::post('remove-player/{playerId}', 'RegistrationController@removePlayer')->name('enlist.updateTeam.removePlayer');
    });
});

/***************************
 * Authentication routes...
 */
// ADMIN
Route::get('login', 'Auth\AdminLoginController@showLoginForm')->name('admin.login.form');
Route::post('login', 'Auth\AdminLoginController@login')->name('admin.login');
Route::get('logout', 'Auth\AdminLoginController@logout')->name('admin.logout');
// PLAYER
Route::get('go', 'Auth\LoginController@showLoginForm')->name('player.login.form');
Route::post('go', 'Auth\LoginController@login')->name('player.login');
Route::get('leave', 'Auth\LoginController@logout')->name('player.logout');

/***************************
 * Admin routes...
 */
Route::group(['middleware' => ['auth:admin', 'admin']], function () {

    // Test game route
    Route::get('/test-game', 'Admin\GameController@overrideInProgress');

    Route::get('/admin', 'Admin\AdminController@index')->name('admin');
    Route::group(['namespace' => 'Admin', 'prefix' => 'admin'], function () {

        // Trash
        Route::get('trash', 'AdminController@trash')->name('admin.trash');
        Route::post('restore/{id}', 'AdminController@restore')->where('id', '[0-9]+')->name('admin.restore');
        Route::delete('trash/{id}', 'AdminController@delete')->where('id', '[0-9]+')->name('admin.delete');

        // Site Messages
        Route::get('site-messages', 'AdminController@siteMessages')->name('admin.siteMessages');
        Route::post('site-messages/{key}', 'AdminController@updateSiteMessage')->name('admin.siteMessages.update');

        //AGENT index, create, show store edit update destroy
        Route::resource('agent', 'AgentController', [
            'except' => ['show'],
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
        Route::resource('evidence', 'EvidenceController', [
            'except' => ['show'],
            'names' => [
                'index' => 'admin.evidence.index',
                'create' => 'admin.evidence.create',
                'store' => 'admin.evidence.store',
                'edit' => 'admin.evidence.edit',
                'update' => 'admin.evidence.update',
                'destroy' => 'admin.evidence.destroy',
            ],
        ]);

        Route::group(['prefix' => 'game/{id}'], function () {

            //Activate
            Route::get('/activate', 'GameController@activate')->where('id', '[0-9]+')->name('admin.game.activate');
            Route::get('/deactivate', 'GameController@deactivate')->where('id', '[0-9]+')->name('admin.game.deactivate');
            //Archive
            Route::get('/archive', 'GameController@editArchive')->where('id', '[0-9]+')->name('admin.game.edit.archive');
            //Evidence Room
            Route::get('/evidence', 'GameController@editEvidence')->where('id', '[0-9]+')->name('admin.game.edit.evidence');
            Route::post('/import-evidence-room', 'GameController@importEvidenceRoom')->where('id', '[0-9]+')->name('admin.game.import-evidence-room');
            //Teams
            Route::get('teams', 'GameController@teams')->where('id', '[0-9]+')->name('admin.game.teams');
            Route::post('add-team', 'GameController@addTeam')->where('id', '[0-9]+')->name('admin.game.addTeam');
            //Judgement
            Route::get('judgement', 'GameController@judgement')->where('id', '[0-9]+')->name('admin.game.judgement');
            Route::post('/judge/{questId}/{questionId}/{teamId}', 'GameController@judgeAnswers')
                ->where('id', '[0-9]+')
                ->where('questId', '[0-9]+')
                ->where('questionId', '[0-9]+')
                ->where('teamId', '[0-9]+')
                ->name('admin.game.judgeAnswers');
            // Scoring
            Route::post('/bonus-points', 'GameController@bonusPoints')->where('id', '[0-9]+')->name('admin.game.bonus');
            Route::get('/score/{includeWaitlist?}', 'GameController@score')
                ->where('id', '[0-9]+')
                ->name('admin.game.score');
            // Player check-in
            Route::get('/check-in', 'GameController@checkIn')
                ->where('id', '[0-9]+')
                ->name('admin.game.checkin');
            Route::post('/player/check-in/{playerId?}', 'GameController@checkInPlayer')->where('id', '[0-9]+')->name('admin.game.checkin.player');

            // GLADOS
            Route::get('glados/viewing', 'GladosController@viewing')->name('admin.game.glados.viewing');
            Route::get('glados/status', 'GladosController@status')->name('admin.game.glados.status');

            // ALERTS
            Route::resource('alert', 'AlertController', ['only' => ['store', 'destroy'], 'names' => [
                'store' => 'admin.game.alert.store',
                'destroy' => 'admin.game.alert.destroy',
            ]]);
        });
        Route::resource('game', 'GameController', [
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
        Route::resource('location', 'LocationController', [
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
        Route::resource('minigame-image', 'MinigameImageController', [
            'except' => ['show'],
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
        Route::resource('ghost-dna', 'GhostDnaController', [
            'names' => [
                'index' => 'admin.ghostDna.index',
                'store' => 'admin.ghostDna.store',
                'destroy' => 'admin.ghostDna.destroy',
            ],
            'except' => [
                'create',
                'show',
                'edit',
                'update',
            ],
        ]);

        //PLAYERS
        Route::resource('player', 'PlayerController', [
            'except' => ['store', 'create'],
            'names' => [
                'index' => 'admin.player.index',
                'show' => 'admin.player.show',
                'edit' => 'admin.player.edit',
                'update' => 'admin.player.update',
                'destroy' => 'admin.player.destroy',
            ],
        ]);

        //QUEST
        Route::post('quest/{id}/add/{questionId}', 'QuestController@addQuestion')->where('id', '[0-9]+')->where('questionId', '[0-9]+');
        Route::post('quest/{id}/remove/{questionId}', 'QuestController@removeQuestion')->where('id', '[0-9]+')->where('questionId', '[0-9]+');
        Route::post('quest/{id}/reorder/{questionId}', 'QuestController@reorderQuestions')->where('id', '[0-9]+')->where('questionId', '[0-9]+');
        Route::resource('game.quest', 'QuestController', [
            'only' => ['edit', 'update'],
            'names' => [
                'edit' => 'admin.game.quest.edit',
                'update' => 'admin.game.quest.update',
            ],
        ]);

        //QUESTION
        Route::delete('answer/{id}', 'QuestionController@destroyAnswer')->where('id', '[0-9]+')->name('admin.destroy.answer');
        Route::get('answer/new', 'QuestionController@newAnswer')->name('admin.new.answer');
        Route::resource('question', 'QuestionController', [
            'except' => ['show'],
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
        Route::resource('suspect', 'SuspectController', [
            'only' => ['index', 'edit', 'update'],
            'names' => [
                'index' => 'admin.suspect.index',
                'edit' => 'admin.suspect.edit',
                'update' => 'admin.suspect.update',
            ],
        ]);

        // TEAM
        Route::post('team/{id}/waitlist', 'TeamController@toggleWaitlist')->where('id', '[0-9]+')->name('admin.team.waitlist');
        Route::delete('team/{id}/removePlayer/{playerId}', 'TeamController@removePlayer')->where('id', '[0-9]+')->where('playerId', '[0-9]+')->name('admin.team.removePlayer');
        Route::post('team/{id}/addPlayer', 'TeamController@addPlayer')->where('id', '[0-9]+')->name('admin.team.addPlayer');
        Route::resource('team', 'TeamController', [
            'only' => ['edit', 'update', 'destroy'],
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

        Route::put('update-homepage-alert', 'SiteController@updateHomepageAlert')->name('admin.site.updateHomepageAlert');
    });
});
