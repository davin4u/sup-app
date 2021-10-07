@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex">
                        <h1 class="text-2xl mb-3 text-gray-700 flex-grow">{{ __('My Activities') }}</h1>

                        <div class="w-1/3 text-right">
                            <a
                                class="p-1 rounded border border-red-300 bg-red-600 text-white text-sm hover:bg-red-500 hover:no-underline"
                                href="{{ route('activities.upload_gpx') }}"
                            >{{ __('Upload GPX') }}</a>
                        </div>
                    </div>

                    <div class="w-full mb-4">
                        @foreach ($activities as $key => $activity)
                            <div class="flex p-2 @if($key % 2 === 0) bg-gray-200 @else bg-gray-100 @endif">
                                <div class="w-2/3">
                                    <div class="text-gray-700">
                                        {{ $activity->name }} / {{ $activity->getActivityTypeName() }} / {{ $activity->getTrainingTypeName() }}
                                    </div>

                                    <div class="text-gray-500 text-xs">{{ __('Distance') . ': ' . $activity->getHumanDistance() . ' | ' . __('Duration') . ': ' . $activity->getHumanDuration() . ' | ' . __('Avg Speed') . ': ' . $activity->getAvgSpeed() }}</div>
                                </div>

                                <div class="w-1/3 text-right self-center">
                                    <a href="{{ route('activities.show', ['activity' => $activity]) }}">{{ __('Details') }}</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(false)
                    <div class="flex w-full mb-2 bg-gray-700 text-white">
                        <div class="w-3/12 p-1">{{ __('Activity Name') }}</div>
                        <div class="w-3/12 p-1">{{ __('Activity Type') }}</div>
                        <div class="w-3/12 p-1">{{ __('Training Type') }}</div>

                        <div class="w-3/12 text-right p-1"></div>
                    </div>

                    @foreach ($activities as $activity)
                        <div class="flex w-full mb-2">
                            <div class="w-3/12 p-1">{{ $activity->name }}</div>
                            <div class="w-3/12 p-1">{{ $activity->getActivityTypeName() }}</div>
                            <div class="w-3/12 p-1">{{ $activity->getTrainingTypeName() }}</div>
                            <div class="w-3/12 text-right p-1">
                                <a href="{{ route('activities.show', ['activity' => $activity]) }}">{{ __('Details') }}</a>
                            </div>
                        </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
