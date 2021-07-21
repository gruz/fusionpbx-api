<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
        <x-lang />

    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="md:flex no-wrap md:-mx-2 ">
                <!-- Left Side -->
                <div class="w-full md:w-3/12 md:mx-2">
                    <!-- Profile Card -->
                    <div class="bg-white p-3 border-t-4 border-green-400">
                        <div class="image overflow-hidden">
                            <img class="h-auto w-full mx-auto" src="/img/profile_stub_eddie.png" alt="">
                        </div>
                        <h3 class="text-gray-600 font-lg text-semibold leading-6">
                            <a class="text-blue-800"
                                href="mailto:{{ Auth::user()->user_email }}">{{ Auth::user()->user_email }}</a>
                        </h3>
                        <ul
                            class="bg-gray-100 text-gray-600 hover:text-gray-700 hover:shadow py-2 px-3 mt-3 divide-y rounded shadow-sm">
                            <li class="flex items-center py-3">
                                <span>{{ __('Domain') }}</span>
                                <span class="ml-auto text-right">
                                    {{ Auth::user()->domain->getAttribute('domain_name') }}

                                </span>
                            </li>
                            <li class="flex items-center py-3">
                                <span>{{ __('Status') }}</span>
                                <span class="ml-auto">
                                    <span
                                        class="{{ Auth::user()->user_enabled === 'true' ? 'bg-green-500' : 'bg-pink-300' }}  py-1 px-2 rounded text-white text-sm">
                                        {{ Auth::user()->user_enabled === 'true' ? __('Active') : __('InActive') }}
                                    </span>
                                </span>
                            </li>
                            <li class="flex items-center py-3">
                                <span>{{ __('Member since') }}</span>
                                <span class="ml-auto text-right">
                                    {{ \Carbon\Carbon::parse(Auth::user()->add_date)->toDateTimeString() }}

                                </span>
                            </li>

                            @if (Auth::user()->getAccountCode())
                            <li class="flex items-center py-3">
                                <span>{{ __('Account reference code') }}</span>
                                <span class="ml-auto text-right">
                                    {{ Auth::user()->getAccountCode() }}

                                </span>
                            </li>
                            @endif

                            <?php
                            $balance = Auth::user()->getCGRTBalanceAttribute();
                            $currency = Auth::user()->getCGRTCurrencyAttribute();
                            ?>
                            @if ($balance !== null )
                            <li class="flex items-center py-3">
                                <span>{{ __('Balance') }}</span>
                                <span class="ml-auto text-right">
                                    {!! $balance !!} {{ $currency }}

                                </span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <!-- Right Side -->
                <div class="w-full md:w-9/12 mx-2 h-64">
                    <!-- Profile tab -->
                    <!-- Extensions Section -->
                    <div class="bg-white p-3 shadow-sm rounded-sm">
                        <div class="flex items-center space-x-2 font-semibold text-gray-900 leading-8">
                            <span clas="text-green-500">
                                <svg class="h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path fill="none"
                                        d="M18.21,16.157v-8.21c0-0.756-0.613-1.368-1.368-1.368h-1.368v1.368v1.368v6.841l-1.368,3.421h5.473L18.21,16.157z">
                                    </path>
                                    <path fill="none"
                                        d="M4.527,9.316V7.948V6.579H3.159c-0.756,0-1.368,0.613-1.368,1.368v8.21l-1.368,3.421h5.473l-1.368-3.421V9.316z">
                                    </path>
                                    <path fill="none"
                                        d="M14.766,5.895h0.023V5.21c0-2.644-2.145-4.788-4.789-4.788S5.211,2.566,5.211,5.21v0.685h0.023H14.766zM12.737,3.843c0.378,0,0.684,0.307,0.684,0.684s-0.306,0.684-0.684,0.684c-0.378,0-0.684-0.307-0.684-0.684S12.358,3.843,12.737,3.843z M10,1.448c0.755,0,1.368,0.613,1.368,1.368S10.755,4.185,10,4.185c-0.756,0-1.368-0.613-1.368-1.368S9.244,1.448,10,1.448z">
                                    </path>
                                    <path fill="none"
                                        d="M14.789,6.579H5.211v9.578l1.368,1.368h6.841l1.368-1.368V6.579z M12.052,12.052H7.948c-0.378,0-0.684-0.306-0.684-0.684c0-0.378,0.306-0.684,0.684-0.684h4.105c0.378,0,0.684,0.306,0.684,0.684C12.737,11.746,12.431,12.052,12.052,12.052z M12.052,9.316H7.948c-0.378,0-0.684-0.307-0.684-0.684s0.306-0.684,0.684-0.684h4.105c0.378,0,0.684,0.307,0.684,0.684S12.431,9.316,12.052,9.316z">
                                    </path>
                                </svg>

                            </span>
                            <span class="tracking-wide">{{ __('My voice accounts') }}</span>
                        </div>
                        <div class="text-gray-700">
                            @foreach (Auth::user()->extensions as $extension)
                                <div class="grid md:grid-cols-2 text-sm">
                                    <div class="grid grid-cols-2">
                                        <div class="px-4 py-2 font-semibold">{{ __('Voice account (Login)') }}</div>
                                        <div class="px-4 py-2">{{ $extension->extension }}</div>
                                        <div class="px-4 py-2 font-semibold">{{ __('Password') }}</div>
                                        <div class="px-4 py-2">{{ $extension->password }}</div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div class="px-4 py-2 font-semibold">{{ __('Effective caller number') }}</div>
                                        <div class="px-4 py-2">{{ $extension->effective_caller_id_number }}</div>
                                        <div class="px-4 py-2 font-semibold">{{ __('Effective caller name') }}</div>
                                        <div class="px-4 py-2">{{ $extension->effective_caller_id_name }}</div>
                                        <div class="px-4 py-2 font-semibold">{{ __('SIP Login') }}</div>
                                        <div class="px-4 py-2"><a class="bg-green-400 hover:bg-green-600 py-1 px-2 rounded text-white text-sm" href="csc:{{ $extension->extension }}{{ '@' . Auth::user()->domain->getAttribute('domain_name') }}:{{ $extension->password }}{{ '@' . $cloudID }}">{{ __('Start the app') }}</a></div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <div class="px-4 py-2 font-semibold">{{ __('Voicemail password') }}</div>
                                        <div class="px-4 py-2">{{ $extension->voicemail->voicemail_password }}</div>
                                        <div class="px-4 py-2 font-semibold">{{ __('Enabled') }}</div>
                                        <div class="px-4 py-2">{{ $extension->enabled }}</div>
                                    </div>
                                </div>
                                <div class="my-10"></div>
                            @endforeach
                        </div>
                    </div>
                    <!-- End of extensions section -->
                    <div class="my-4"></div>

                    @if (Auth::user()->getAccountCode())
                        <!-- Payment -->
                        <div class="bg-white p-3 shadow-sm rounded-sm">
                            <div class="flex items-center space-x-2 font-semibold text-gray-900 leading-8">
                                <span clas="text-green-500">
                                    <svg class="h-5" viewBox="0 0 20 20" stroke="currentColor">
                                        <path fill="none" d="M9.941,4.515h1.671v1.671c0,0.231,0.187,0.417,0.417,0.417s0.418-0.187,0.418-0.417V4.515h1.672c0.229,0,0.417-0.187,0.417-0.418c0-0.23-0.188-0.417-0.417-0.417h-1.672V2.009c0-0.23-0.188-0.418-0.418-0.418s-0.417,0.188-0.417,0.418V3.68H9.941c-0.231,0-0.418,0.187-0.418,0.417C9.522,4.329,9.71,4.515,9.941,4.515 M17.445,15.479h0.003l1.672-7.52l-0.009-0.002c0.009-0.032,0.021-0.064,0.021-0.099c0-0.231-0.188-0.417-0.418-0.417H5.319L4.727,5.231L4.721,5.232C4.669,5.061,4.516,4.933,4.327,4.933H1.167c-0.23,0-0.418,0.188-0.418,0.417c0,0.231,0.188,0.418,0.418,0.418h2.839l2.609,9.729h0c0.036,0.118,0.122,0.214,0.233,0.263c-0.156,0.254-0.25,0.551-0.25,0.871c0,0.923,0.748,1.671,1.67,1.671c0.923,0,1.672-0.748,1.672-1.671c0-0.307-0.088-0.589-0.231-0.836h4.641c-0.144,0.247-0.231,0.529-0.231,0.836c0,0.923,0.747,1.671,1.671,1.671c0.922,0,1.671-0.748,1.671-1.671c0-0.32-0.095-0.617-0.252-0.871C17.327,15.709,17.414,15.604,17.445,15.479 M15.745,8.275h2.448l-0.371,1.672h-2.262L15.745,8.275z M5.543,8.275h2.77L8.5,9.947H5.992L5.543,8.275z M6.664,12.453l-0.448-1.671h2.375l0.187,1.671H6.664z M6.888,13.289h1.982l0.186,1.671h-1.72L6.888,13.289zM8.269,17.466c-0.461,0-0.835-0.374-0.835-0.835s0.374-0.836,0.835-0.836c0.462,0,0.836,0.375,0.836,0.836S8.731,17.466,8.269,17.466 M11.612,14.96H9.896l-0.186-1.671h1.901V14.96z M11.612,12.453H9.619l-0.186-1.671h2.18V12.453zM11.612,9.947H9.34L9.154,8.275h2.458V9.947z M14.162,14.96h-1.715v-1.671h1.9L14.162,14.96z M14.441,12.453h-1.994v-1.671h2.18L14.441,12.453z M14.72,9.947h-2.272V8.275h2.458L14.72,9.947z M15.79,17.466c-0.462,0-0.836-0.374-0.836-0.835s0.374-0.836,0.836-0.836c0.461,0,0.835,0.375,0.835,0.836S16.251,17.466,15.79,17.466 M16.708,14.96h-1.705l0.186-1.671h1.891L16.708,14.96z M15.281,12.453l0.187-1.671h2.169l-0.372,1.671H15.281z"></path>
                                    </svg>
                                </span>
                                <span class="tracking-wide">{{ __('Replenish the balance') }}</span>
                            </div>
                            <div class="grid grid-cols-2 bg-gray">
                                <div>
                                    {{-- @include('layouts.stripe-payment') --}}
                                    <x-stripe-card />
                                </div>
                            </div>
                            <!-- Payment -->
                        </div>
                        <!-- End of profile tab -->
                    </div>
                    @endif
            </div>
        </div>
    </div>
</x-app-layout>
