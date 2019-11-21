@extends('layouts.asset_edit', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'question',
    'model' => $question,
    'delete_message' => 'Are you sure you want to delete this question?',
    'page_title' => 'Edit #'.$question->id,
    'locations' => $locations,
    'incorrect' => $incorrect
])