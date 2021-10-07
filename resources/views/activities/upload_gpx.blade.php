@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="text-xl mb-2 p-6 bg-gray-200 text-gray-800 border-b border-gray-300">{{ __('Upload your GPX file') }}</div>

                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('activities.store_gpx') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="w-1/2">
                            <div>
                                <input type="file" name="gpx" class="w-full" />
                            </div>

                            <div class="mt-4">
                                <label class="text-lg font-bold">{{ __('Activity name') }}</label>

                                <input
                                    type="text"
                                    name="name"
                                    class="w-full p-2 rounded-lg border border-gray-200"
                                />
                            </div>

                            <div class="mt-4">
                                <label class="text-lg font-bold">{{ __('Activity type') }}</label>

                                <select name="activity_type" class="w-full p-2 rounded-lg border border-gray-200">
                                    <option value="">-</option>

                                    @foreach($activityTypes as $typeKey => $typeName)
                                        <option value="{{ $typeKey }}">{{ $typeName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-4">
                                <label class="text-lg font-bold">{{ __('Training type') }}</label>

                                <select name="training_type" class="w-full p-2 rounded-lg border border-gray-200">
                                    <option value="">-</option>

                                    @foreach($trainingTypes as $typeKey => $typeName)
                                        <option value="{{ $typeKey }}">{{ $typeName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="py-2 px-4 rounded-lg border border-red-300 bg-red-600 text-white hover:bg-red-500">{{ __('Upload') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
