@props(['errors'])

@if ($errors->any())
    <div {{ $attributes }}>
        <div class="font-medium text-red-600">
            {{ __('Whoops! Something went wrong.') }}
        </div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
            @foreach ($errors->all() as $k => $error)
                @if ($error !== '')
                    <li>{{ $error }}</li>
                @endif
            @endforeach
        </ul>
    </div>
@endif
