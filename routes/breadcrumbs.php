<?php

/***************************************************************
 * Admin
 */
//-------------- INDEX ------------------//
Breadcrumbs::register('admin',function($breadcrumbs)
{
    $breadcrumbs->push('Admin',route('admin'));
});
Breadcrumbs::register('admin.trash',function($breadcrumbs)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Deleted Games',route('admin.trash'));
});
Breadcrumbs::register('admin.siteMessages',function($breadcrumbs)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Site Messages',route('admin.siteMessages'));
});

/***************************************************************
 * Game
 */
//-------------- CREATE ------------------//
Breadcrumbs::register('admin.game.create',function($breadcrumbs,$game)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Create New Game',route('admin.game.create'));
});
//-------------- SHOW ------------------//
Breadcrumbs::register('admin.game.show',function($breadcrumbs,$game)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push($game->name,route('admin.game.show',$game->id));
});
//-------------- EDIT ------------------//
Breadcrumbs::register('admin.game.edit',function($breadcrumbs,$game)
{
    $breadcrumbs->parent('admin.game.show',$game);
    $breadcrumbs->push('Settings',route('admin.game.edit',$game->id));
});
//-------------- TEAMS ------------------//
Breadcrumbs::register('admin.game.teams',function($breadcrumbs,$game)
{
    $breadcrumbs->parent('admin.game.show',$game);
    $breadcrumbs->push('Teams',route('admin.game.teams',$game->id));
});
//-------------- ARCHIVE ------------------//
Breadcrumbs::register('admin.game.archive',function($breadcrumbs,$game)
{
    $breadcrumbs->parent('admin.game.show',$game);
    $breadcrumbs->push('Archive Data',route('admin.game.edit.archive',$game->id));
});
//-------------- EVIDENCE ------------------//
Breadcrumbs::register('admin.game.edit.evidence',function($breadcrumbs,$game)
{
    $breadcrumbs->parent('admin.game.edit',$game);
    $breadcrumbs->push('Evidence Room',route('admin.game.edit.evidence',$game->id));
});
//-------------- JUDGEMENT ------------------//
Breadcrumbs::register('admin.game.judgement',function($breadcrumbs,$game)
{
    $breadcrumbs->parent('admin.game.score',$game);
    $breadcrumbs->push('Judge Answers',route('admin.game.judgement',$game->id));
});
//-------------- SCORE ------------------//
Breadcrumbs::register('admin.game.score',function($breadcrumbs,$game)
{
    $breadcrumbs->parent('admin.game.show',$game);
    $breadcrumbs->push('Score',route('admin.game.score',$game->id));
});
//-------------- PLAYER CHECK-IN ------------------//
Breadcrumbs::register('admin.game.checkin',function($breadcrumbs,$game)
{
    $breadcrumbs->parent('admin.game.show',$game);
    $breadcrumbs->push('Player Check-in',route('admin.game.checkin',$game->id));
});

/***************************************************************
 * Quest
 */
//-------------- QUEST EDIT ------------------//
Breadcrumbs::register('admin.game.quest.edit',function($breadcrumbs,$game,$quest,$location)
{
    $breadcrumbs->parent('admin.game.edit',$game);
    $breadcrumbs->push($location->name,route('admin.game.quest.edit',array($game->id,$quest->id)));
});

/***************************************************************
 * Team
 */
//-------------- EDIT ------------------//
Breadcrumbs::register('admin.team.edit',function($breadcrumbs,$team)
{
    $breadcrumbs->parent('admin.game.teams',$team->game);
    $breadcrumbs->push($team->name,route('admin.team.edit',$team->id));
});

/***************************************************************
 * Questions
 */
//-------------- INDEX ------------------//
Breadcrumbs::register('admin.question.index',function($breadcrumbs)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Question Pool',route('admin.question.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::register('admin.question.edit',function($breadcrumbs,$question)
{
    $breadcrumbs->parent('admin.question.index',$question);
    $breadcrumbs->push('Edit',route('admin.question.edit',$question->id));
});
//-------------- CREATE ------------------//
Breadcrumbs::register('admin.question.create',function($breadcrumbs)
{
    $breadcrumbs->parent('admin.question.index');
    $breadcrumbs->push('New Question',route('admin.question.create'));
});

/***************************************************************
 * Evidence
 */
//-------------- INDEX ------------------//
Breadcrumbs::register('admin.evidence.index',function($breadcrumbs)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Evidence',route('admin.evidence.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::register('admin.evidence.edit',function($breadcrumbs,$evidence)
{
    $breadcrumbs->parent('admin.evidence.index',$evidence);
    $breadcrumbs->push('Edit',route('admin.evidence.edit',$evidence->id));
});
//-------------- CREATE ------------------//
Breadcrumbs::register('admin.evidence.create',function($breadcrumbs)
{
    $breadcrumbs->parent('admin.evidence.index');
    $breadcrumbs->push('New Evidence',route('admin.evidence.create'));
});

/***************************************************************
 * Agents
 */
//-------------- INDEX ------------------//
Breadcrumbs::register('admin.agent.index',function($breadcrumbs)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Agents',route('admin.agent.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::register('admin.agent.edit',function($breadcrumbs,$agent)
{
    $breadcrumbs->parent('admin.agent.index');
    $breadcrumbs->push($agent->full_name,route('admin.agent.edit',$agent->id));
});
//-------------- CREATE ------------------//
Breadcrumbs::register('admin.agent.create',function($breadcrumbs)
{
    $breadcrumbs->parent('admin.agent.index');
    $breadcrumbs->push('Add New Agent',route('admin.agent.create'));
});

/***************************************************************
 * Suspects
 */
//-------------- INDEX ------------------//
Breadcrumbs::register('admin.suspect.index',function($breadcrumbs)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Suspects',route('admin.suspect.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::register('admin.suspect.edit',function($breadcrumbs,$suspect)
{
    $breadcrumbs->parent('admin.suspect.index');
    $breadcrumbs->push($suspect->name,route('admin.suspect.edit',$suspect->id));
});

/***************************************************************
 * Locations
 */
//-------------- INDEX ------------------//
Breadcrumbs::register('admin.location.index',function($breadcrumbs)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Locations',route('admin.location.index'));
});
//-------------- CREATE ------------------//
Breadcrumbs::register('admin.location.create',function($breadcrumbs)
{
    $breadcrumbs->parent('admin.location.index');
    $breadcrumbs->push('Add New Location',route('admin.location.create'));
});
//-------------- EDIT ------------------//
Breadcrumbs::register('admin.location.edit',function($breadcrumbs,$location)
{
    $breadcrumbs->parent('admin.location.index');
    $breadcrumbs->push($location->name,route('admin.location.edit',$location->id));
});

/***************************************************************
 * Minigame Images
 */
//-------------- INDEX ------------------//
Breadcrumbs::register('admin.minigameImage.index',function($breadcrumbs)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Minigame Images',route('admin.minigameImage.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::register('admin.minigameImage.edit',function($breadcrumbs,$minigameImage)
{
    $breadcrumbs->parent('admin.minigameImage.index');
    $breadcrumbs->push($minigameImage->year,route('admin.minigameImage.edit',$minigameImage->id));
});
//-------------- CREATE ------------------//
Breadcrumbs::register('admin.minigameImage.create',function($breadcrumbs)
{
    $breadcrumbs->parent('admin.minigameImage.index');
    $breadcrumbs->push('Add New Minigame Image',route('admin.minigameImage.create'));
});
/***************************************************************
 * Ghost DNA
 */
//-------------- INDEX ------------------//
Breadcrumbs::register('admin.ghostDna.index',function($breadcrumbs)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Ghost Dna',route('admin.ghostDna.index'));
});

/***************************************************************
 * Players
 */
//-------------- INDEX ------------------//
Breadcrumbs::register('admin.player.index',function($breadcrumbs)
{
    $breadcrumbs->parent('admin');
    $breadcrumbs->push('Players',route('admin.player.index'));
});
//-------------- EDIT ------------------//
Breadcrumbs::register('admin.player.edit',function($breadcrumbs,$player)
{
    $breadcrumbs->parent('admin.player.index');
    $breadcrumbs->push($player->full_name,route('admin.player.edit',$player->id));
});