@extends('admin.layout')

@section('title', $film->title)

@section('content')
    @include('films._show-content')
@endsection
