@extends('layouts.asset_index', [
    'title' => 'Clue - Game Admin',
    'model_name' => 'evidence',
    'models' => $evidence,
    'page_title' => 'Game Evidence'
])