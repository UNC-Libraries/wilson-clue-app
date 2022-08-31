<?php

/***************************************************************
 * Admin
 */
//-------------- INDEX ------------------//
Breadcrumbs::for('admin', function ($trail) {
    $trail->push('Admin', route('admin'));
});
Breadcrumbs::for('admin.trash', function ($trail) {
    $trail->parent('admin');
    $trail->push('Deleted Games', route('admin.trash'));
});
Breadcrumbs::for('admin.siteMessages', function ($trail) {
    $trail->parent('admin');
    $trail->push('Site Messages', route('admin.siteMessages'));
});

/***************************************************************
 * Game
 */
//-------------- CREATE ------------------//
Breadcrumbs::for('admin.game.create', function ($trail, $game) {
    $trail->parent('admin');
    $trail->push('Create New Game', route('admin.game.create'));
});
//-------------- SHOW ------------------//
Breadcrumbs::for('admin.game.show', function ($trail, $game) {
    $trail->parent('admin');
    $trail->push($game->name, route('admin.game.show', $game->id));
});
//-------------- EDIT ------------------//
Breadcrumbs::for('admin.game.edit', function ($trail, $game) {
    $trail->parent('admin.game.show', $game);
    $trail->push('Settings', route('admin.game.edit', $game->id));
});
//-------------- TEAMS ------------------//
Breadcrumbs::for('admin.game.teams', function ($trail, $game) {
    $trail->parent('admin.game.show', $game);
    $trail->push('Teams', route('admin.game.teams', $game->id));
});
//-------------- ARCHIVE ------------------//
Breadcrumbs::for('admin.game.archive', function ($trail, $game) {
    $trail->parent('admin.game.show', $game);
    $trail->push('Archive Data', route('admin.game.edit.archive', $game->id));
});
//-------------- EVIDENCE ------------------//
Breadcrumbs::for('admin.game.edit.evidence', function ($trail, $game) {
    $trail->parent('admin.game.edit', $game);
    $trail->push('Evidence Room', route('admin.game.edit.evidence', $game->id));
});
//-------------- JUDGEMENT ------------------//
Breadcrumbs::for('admin.game.judgement', function ($trail, $game) {
    $trail->parent('admin.game.score', $game);
    $trail->push('Judge Answers', route('admin.game.judgement', $game->id));
});
//-------------- SCORE ------------------//
Breadcrumbs::for('admin.game.score', function ($trail, $game) {
    $trail->parent('admin.game.show', $game);
    $trail->push('Score', route('admin.game.score', $game->id));
});
//-------------- PLAYER CHECK-IN ------------------//
Breadcrumbs::for('admin.game.checkin', function ($trail, $game) {
    $trail->parent('admin.game.show', $game);
    $trail->push('Player Check-in', route('admin.game.checkin', $game->id));
});

/***************************************************************
 * Quest
 */
//-------------- QUEST EDIT ------------------//
Breadcrumbs::for('admin.game.quest.edit', function ($trail, $game, $quest, $location) {
    $trail->parent('admin.game.edit', $game);
    $trail->push($location->name, route('admin.game.quest.edit', [$game->id, $quest->id]));
});

/***************************************************************
 * Team
 */
//-------------- EDIT ------------------//
Breadcrumbs::for('admin.team.edit', function ($trail, $team) {
    $trail->parent('admin.game.teams', $team->game);
    $trail->push($team->name, route('admin.team.edit', $team->id));
});

/***************************************************************
 * Questions
 */
//-------------- INDEX ------------------//
Breadcrumbs::for('admin.question.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Question Pool', route('admin.question.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::for('admin.question.edit', function ($trail, $question) {
    $trail->parent('admin.question.index', $question);
    $trail->push('Edit', route('admin.question.edit', $question->id));
});
//-------------- CREATE ------------------//
Breadcrumbs::for('admin.question.create', function ($trail) {
    $trail->parent('admin.question.index');
    $trail->push('New Question', route('admin.question.create'));
});

/***************************************************************
 * Evidence
 */
//-------------- INDEX ------------------//
Breadcrumbs::for('admin.evidence.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Evidence', route('admin.evidence.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::for('admin.evidence.edit', function ($trail, $evidence) {
    $trail->parent('admin.evidence.index', $evidence);
    $trail->push('Edit', route('admin.evidence.edit', $evidence->id));
});
//-------------- CREATE ------------------//
Breadcrumbs::for('admin.evidence.create', function ($trail) {
    $trail->parent('admin.evidence.index');
    $trail->push('New Evidence', route('admin.evidence.create'));
});

/***************************************************************
 * Agents
 */
//-------------- INDEX ------------------//
Breadcrumbs::for('admin.agent.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Agents', route('admin.agent.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::for('admin.agent.edit', function ($trail, $agent) {
    $trail->parent('admin.agent.index');
    $trail->push($agent->full_name, route('admin.agent.edit', $agent->id));
});
//-------------- CREATE ------------------//
Breadcrumbs::for('admin.agent.create', function ($trail) {
    $trail->parent('admin.agent.index');
    $trail->push('Add New Agent', route('admin.agent.create'));
});

/***************************************************************
 * Suspects
 */
//-------------- INDEX ------------------//
Breadcrumbs::for('admin.suspect.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Suspects', route('admin.suspect.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::for('admin.suspect.edit', function ($trail, $suspect) {
    $trail->parent('admin.suspect.index');
    $trail->push($suspect->name, route('admin.suspect.edit', $suspect->id));
});

/***************************************************************
 * Locations
 */
//-------------- INDEX ------------------//
Breadcrumbs::for('admin.location.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Locations', route('admin.location.index'));
});
//-------------- CREATE ------------------//
Breadcrumbs::for('admin.location.create', function ($trail) {
    $trail->parent('admin.location.index');
    $trail->push('Add New Location', route('admin.location.create'));
});
//-------------- EDIT ------------------//
Breadcrumbs::for('admin.location.edit', function ($trail, $location) {
    $trail->parent('admin.location.index');
    $trail->push($location->name, route('admin.location.edit', $location->id));
});

/***************************************************************
 * Minigame Images
 */
//-------------- INDEX ------------------//
Breadcrumbs::for('admin.minigameImage.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Minigame Images', route('admin.minigameImage.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::for('admin.minigameImage.edit', function ($trail, $minigameImage) {
    $trail->parent('admin.minigameImage.index');
    $trail->push($minigameImage->year, route('admin.minigameImage.edit', $minigameImage->id));
});
//-------------- CREATE ------------------//
Breadcrumbs::for('admin.minigameImage.create', function ($trail) {
    $trail->parent('admin.minigameImage.index');
    $trail->push('Add New Minigame Image', route('admin.minigameImage.create'));
});
/***************************************************************
 * Ghost DNA
 */
//-------------- INDEX ------------------//
Breadcrumbs::for('admin.ghostDna.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Ghost Dna', route('admin.ghostDna.index'));
});

/***************************************************************
 * Players
 */
//-------------- INDEX ------------------//
Breadcrumbs::for('admin.player.index', function ($trail) {
    $trail->parent('admin');
    $trail->push('Players', route('admin.player.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::for('admin.player.edit', function ($trail, $player) {
    $trail->parent('admin.player.index');
    $trail->push($player->full_name, route('admin.player.edit', $player->id));
});
