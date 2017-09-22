@extends('layouts.asset_index', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'agent',
    'models' => $agents,
])