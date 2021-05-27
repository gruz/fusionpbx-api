{{-- <div class="fixed top-0 left-0 px-6 py-4 sm:block"> --}}
    @foreach ($langs as $lang)
        <a href="{{ route('lang', $lang) }}" class="m-2  text-2xl {{ App::currentLocale() === $lang ? 'text-gray-200 ' : 'text-gray-500 underline' }} ">{{ $lang }}</a>
    @endforeach
    {{-- <span class="mr-3 text-gray-700">|</span> --}}
{{-- </div> --}}
