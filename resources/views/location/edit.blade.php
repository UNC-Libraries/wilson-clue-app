@extends('layouts.asset_edit', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'location',
    'model' => $location,
    'delete_message' => 'Are your sure you want to delete '.$location->name.'?',
    'page_title' => 'Edit '.$location->name,
    'mapSections' => $mapSections,
])