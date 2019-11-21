@extends('layouts.asset_edit', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'minigameImage',
    'model' => $minigameImage,
    'delete_message' => 'Are you sure you want to delete '.$minigameImage->name.'?',
    'page_title' => 'Edit '.$minigameImage->name,
])