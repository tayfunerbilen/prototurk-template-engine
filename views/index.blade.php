@extends('layout')

@section('meta')
    <title>{{ $title }}</title>
    <meta name="description" content="burası ana sayfa">
    @style('css/index.css')
@endsection

@section('content')

    @if($name == '')
        test
    @elseif($name == 'adana')
        test2
    @else
        test3
    @endif

    @empty($name)
        test4
    @endif

    <h3 class="title3">
        Hoşgeldin, {{ $name }}
    </h3>

    <ul>
        @forelse($todos as $todo)
            @include('static.todo')
        @empty
            <h3>todo bulunamadı :(</h3>
        @endforelse
    </ul>

    @php
        $test = "deneme";
    @endphp

    {{ $test }}

    @dump($todos)
    @dd($todos)

@endsection

@section('sidebar')
    <h3>Kategoriler</h3>
@endsection

@section('script')
    <script>
        const todos = @json($todos);
        console.log(todos);
    </script>
@endsection

@section('style')
    @style
    body {
    background: orangered;
    }
    @endstyle
@endsection