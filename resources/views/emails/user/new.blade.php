@component('mail::message')
# @lang('A new user registered')

@lang('A new user registered to your domain')

<br>
**{{ $user->username }}**


@component('mail::button', ['url' => $url ])
@lang('Activate user')
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
