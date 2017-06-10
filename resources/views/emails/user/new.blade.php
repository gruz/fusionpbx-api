@component('mail::message')
# @lang('A new user registered')

@lang('A new user registered to your domain :domain', ['domain' => $user->domain->domain_name])


@lang('Username'): **{{ $user->username }}**


@component('mail::button', ['url' => $url ])
@lang('Activate user')
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
