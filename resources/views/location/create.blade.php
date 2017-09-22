@extends('layouts.asset_create', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'location',
    'model' => $location,
    'page_title' => 'Create new location',
    'mapSections' => $mapSections,
])