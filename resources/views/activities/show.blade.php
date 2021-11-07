@extends('layouts.app')

@section('head-styles')
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.3.1/mapbox-gl.css' rel='stylesheet' />
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="bg-white border-b border-gray-200">
                <div class="text-xl mb-2 p-6 bg-gray-200 text-gray-800 border-b border-gray-300">
                    {{ $activity->name }} <span class="text-sm text-gray-400">{{ $activity->formattedDate() }}</span>
                </div>

                <div class="p-6">
                    <ActivityStats></ActivityStats>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
