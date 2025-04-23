@extends('layouts.blog-app')

@section('content')
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Post Header -->
            <div class="p-6 md:p-8">
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <span>{{ $post->created_at->format('Y/m/d') }}</span>
                    <span class="mx-2">•</span>
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="text-indigo-600 hover:text-indigo-800">{{ $post->category->name }}</a>
                    <span class="mx-2">•</span>
                    <span>{{ $post->user->name }}</span>
                </div>

                <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>

                <!-- Post Navigation -->
                <div class="flex justify-between text-sm mb-6">
                    <a href="{{ route('blog.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        بازگشت به وبلاگ
                    </a>

                    <!-- Share Links -->
                    <div class="flex space-x-3 space-x-reverse">
                        <a href="#" class="text-gray-500 hover:text-indigo-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-indigo-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-indigo-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M21.582 6.186c-.273-.047-.578-.077-.889-.077-3.003 0-5.436 2.433-5.436 5.436 0 .311.03.616.077.889h-6.667c.047-.273.077-.578.077-.889 0-3.003-2.433-5.436-5.436-5.436-.311 0-.616.03-.889.077v-3.668c0-.273-.227-.5-.5-.5h-1.5c-.273 0-.5.227-.5.5v3.668c-.273.047-.578.077-.889.077-3.003 0-5.436 2.433-5.436 5.436 0 .311.03.616.077.889v6.667c-.047.273-.077.578-.077.889 0 3.003 2.433 5.436 5.436 5.436.311 0 .616-.03.889-.077h6.667c-.047.273-.077.578-.077.889 0 3.003 2.433 5.436 5.436 5.436.311 0 .616-.03.889-.077v3.668c0 .273.227.5.5.5h1.5c.273 0 .5-.227.5-.5v-3.668c.273-.047.578-.077.889-.077 3.003 0 5.436-2.433 5.436-5.436 0-.311-.03-.616-.077-.889h-6.667c.047-.273.077-.578.077-.889 0-3.003-2.433-5.436-5.436-5.436-.311 0-.616.03-.889.077v-6.667zm-14.5 14c-.311 0-.615-.03-.889-.077h-4.147c-.047-.273-.077-.578-.077-.889 0-3.003 2.433-5.436 5.436-5.436.311 0 .616.03.889.077h4.147c.047.273.077.578.077.889 0 3.003-2.433 5.436-5.436 5.436zm14.5-7.436c.311 0 .615.03.889.077h4.147c.047.273.077.578.077.889 0 3.003-2.433 5.436-5.436 5.436-.311 0-.616-.03-.889-.077h-4.147c-.047-.273-.077-.578-.077-.889 0-3.003 2.433-5.436 5.436-5.436zm0-7.436c3.003 0 5.436 2.433 5.436 5.436 0 .311-.03.616-.077.889h-4.147c-.273-.047-.578-.077-.889-.077-3.003 0-5.436 2.433-5.436 5.436 0 .311.03.616.077.889h-4.147c-.273-.047-.578-.077-.889-.077-3.003 0-5.436 2.433-5.436 5.436 0 .311.03.616.077.889h-4.147c-.047-.273-.077-.578-.077-.889 0-3.003 2.433-5.436 5.436-5.436.311 0 .616.03.889.077h4.147c-.047-.273-.077-.578-.077-.889 0-3.003 2.433-5.436 5.436-5.436.311 0 .616.03.889.077h4.147c-.047-.273-.077-.578-.077-.889 0-3.003 2.433-5.436 5.436-5.436z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Featured Image -->
            @if($post->featured_image)
                <div class="w-full">
                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-auto object-cover max-h-96">
                </div>
            @endif

            <!-- Post Content -->
            <div class="p-6 md:p-8">
                <div class="prose max-w-none prose-lg prose-indigo">
                    {!! $post->purified_content !!}
                </div>

                <!-- Tags (if available) -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <div class="flex flex-wrap gap-2">
                        <span class="font-medium text-gray-700">برچسب‌ها:</span>
                        <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm hover:bg-indigo-100 transition">کتاب</a>
                        <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm hover:bg-indigo-100 transition">کتابخوانی</a>
                        <a href="#" class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm hover:bg-indigo-100 transition">روانشناسی</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Posts -->
        <div class="mt-12">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">مطالب مرتبط</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedPosts as $relatedPost)
                    <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300">
                        @if($relatedPost->featured_image)
                            <div class="h-40 overflow-hidden">
                                <img src="{{ asset('storage/' . $relatedPost->featured_image) }}" alt="{{ $relatedPost->title }}" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                            </div>
                        @else
                            <div class="h-40 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
                                <svg class="w-12 h-12 text-indigo-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @endif
                        <div class="p-4">
                            <h4 class="text-lg font-bold mb-2">{{ $relatedPost->title }}</h4>
                            <p class="text-gray-600 text-sm mb-2">{{ Str::limit(strip_tags($relatedPost->content), 80) }}</p>
                            <a href="{{ route('blog.show', $relatedPost->slug) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">ادامه مطلب</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
