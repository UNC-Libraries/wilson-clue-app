@extends('layouts.asset_edit', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'evidence',
    'model' => $evidence,
    'delete_message' => 'Are your sure you want to delete '.$evidence->title.'?',
    'page_title' => 'Edit '.$evidence->title,
])