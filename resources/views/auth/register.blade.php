<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo />
            </a>
        </x-slot>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <x-form :action="route('register')">
            <x-select-domain />
            <?php $sublabel = __('First letter, alphanumeric symbols and dash, 6-32 chars length'); ?>
            <x-form-input type="email" name="user_email" :label="__('Email')" required />
            {{-- <x-form-input name="username" :label="__('Unique username') . '<br/><small>' . $sublabel . '</small>' " required autofocus /> --}}
            
            <?php $sublabel = __('Min 6 symbols, case sensitive, at least one lowercase, one uppercase and one digit'); ?>
            <x-form-input name="password" :label="__('Password') . '<br/><small>' . $sublabel . '</small>'" required autocomplete="new-password" type="password" />
            {{-- <x-form-input name="password_confirmation" :label="__('Confirm password')" required type="password"/> --}}
            
            <x-form-input name="effective_caller_id_name" :label="__('Display name for calls')" required />

            <?php
            $sublabel =  __('4-10 digits');
            $label = __('Voicemail password') . '<br/><small>' . $sublabel . '</small>';
             ?>
            <x-form-input name="voicemail_password" pattern="[0-9]+" :label="$label" required
                autocomplete="new-password" type="password" inputmode="numeric" />
            {{-- <x-form-input name="voicemail_password_confirmation" pattern="[0-9]+" :label="__('Confirm voicemail password')" required type="password"/> --}}

            <?php $sublabel = __('Get it from your reseller or contact <a href="mailto::email">:email</a>',[ 'email' => config('mail.contact_email') ] ); ?>
            <x-form-input name="reseller_reference_code" :label="__('Reseller code') . '<br/><small>' . $sublabel . '</small>'" required />

            <div class="mt-10">
            </div>

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

            <x-captcha />

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
