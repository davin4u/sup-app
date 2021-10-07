@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <a
                        class="p-2 rounded border border-gray-200 hover:bg-gray-100 hover:no-underline"
                        href="{{ route('activities.upload_gpx') }}"
                    >{{ __('Upload GPX') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
