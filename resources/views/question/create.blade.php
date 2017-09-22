@extends('layouts.asset_create', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'question',
    'model' => $question,
    'page_title' => 'Create a new question',
    'locations' => $locations
])