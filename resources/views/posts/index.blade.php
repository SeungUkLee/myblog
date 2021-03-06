@extends('layouts.app')

@section('content')
    <h1 class="page-header">
        <a href="{{ route('posts.create') }}" class="btn btn-primary pull-right">New Post</a>
        List of Posts
    </h1>

    <div class="col-md-3">
        @include('tags.index')
    </div>
    <ul class="col-md-9">
        @foreach ($posts as $post)
            <li>
                <a href="{{ route('posts.show', $post->id) }}">
                    {{ $post->title }}
                </a>
                <small>
                    {{ $post->created_at->diffForHumans() }}
                    by {{ $post->user->name }}
                </small>
            </li>
        @endforeach
    </ul>

    <div class="text-center">
        {{--{{ $posts->render() }}--}}
        {!! $posts->appends(request()->except('page'))->links() !!}
    </div>
@endsection
