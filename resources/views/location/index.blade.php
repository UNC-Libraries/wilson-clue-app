@extends('layouts.asset_index', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'location',
    'models' => $locations,
])