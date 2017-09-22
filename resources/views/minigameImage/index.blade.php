@extends('layouts.asset_index', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'minigameImage',
    'models' => $minigameImages,
    'page_title' => 'Minigame Images',
])