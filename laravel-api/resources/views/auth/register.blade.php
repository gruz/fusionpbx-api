<?php $class = 'rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 '; ?>
<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <x-form :action="route('register')">
            <x-form-select name="domain_uuid" :options="$domains" :class="$class" :label="__('Domain')"/>
            <x-form-input name="username" :label="__('Username')" :class="$class" required autofocus />
            <x-form-input name="effective_caller_id_name" :label="__('Effective Caller ID name')" :class="$class" required autofocus />
            <x-form-input name="password" :label="__('Password')" :class="$class" required autocomplete="new-password" type="password"/>
            {{-- <x-form-input name="password_confirmation" :label="__('Confirm password')" :class="$class" required type="password"/> --}}

            <x-form-input type="email" name="user_email" :class="$class" :label="__('Email')" required />

            <?php $label = __('Voicemail password') .' (' . __('Only digits') . ')'; ?>
            <x-form-input name="voicemail_password" pattern="[0-9]+" :label="$label" :class="$class" required autocomplete="new-password" type="password"/>
            {{-- <x-form-input name="voicemail_password_confirmation" pattern="[0-9]+" :label="__('Confirm voicemail password')" :class="$class" required type="password"/> --}}

            <x-form-input name="reseller_reference_code" :label="__('Reference code')" :class="$class" required />

            {{-- <div class="mt-10">
            </div> --}}

            {{-- <!-- Name -->
            <div class="mt-4">
                <x-label for="name" :value="__('Name')" />

                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-label for="email" :value="__('Email')" />

                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-label for="password" :value="__('Password')" />

                <x-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required />
            </div> --}}

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ml-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </x-form>
    </x-auth-card>
</x-guest-layout>
