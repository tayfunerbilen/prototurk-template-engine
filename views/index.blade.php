@extends('layout')

@section('meta')
    <title>{{ $title }}</title>
    <meta name="description" content="burası ana sayfa">
    @style('css/index.css')
@endsection

@section('content')

    <h3 class="title3">
        Hoşgeldin, {{ $name }}
    </h3>

    <ul>
        @foreach($todos as $todo)
            @include('static.todo')
        @endforeach
    </ul>

    @php
    $test = "deneme";
    @endphp

    {{ $test }}

@endsection

@section('sidebar')
    <h3>Kategoriler</h3>
@endsection

@section('style')
    @style
        body {
            background: orangered;
        }
    @endstyle
@endsection