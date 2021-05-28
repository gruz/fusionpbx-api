@if (Route::has('login'))
<div class="px-6 py-4 sm:block text-xl">
        <x-lang />
        <br />
        @auth
            <a href="{{ url('/dashboard') }}" class="text-gray-500 underline">{{ __('Dashboard') }}</a>
        @else
            <a href="{{ route('login') }}" class="text-gray-500 underline">{{ __('Log in') }}</a>

            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="ml-3 text-gray-500 underline">{{ __('Register') }}</a>
            @endif
        @endauth

    </div>
@endif