@component('mail::message')
# @lang('Password reset has been requested')

@lang('Password reset has been requested at your domain :domain', ['domain' => $user->domain->domain_name])


@lang('Username'): **{{ $user->username }}**


@component('mail::button', ['url' => $url ])
@lang('Reset password')
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
