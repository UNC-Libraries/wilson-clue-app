@extends('layouts.asset_edit', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'agent',
    'model' => $agent,
    'delete_message' => 'Are your sure you want to delete '.$agent->full_name.'?',
    'page_title' => 'Edit '.$agent->full_name,
])