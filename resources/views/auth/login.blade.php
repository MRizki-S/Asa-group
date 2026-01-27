<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {{-- csrf token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Asa Group' }}</title>
    @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.js'])


    <link rel="icon" href="{{ asset('images/logo/logo-abm.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/logo/logo-abm.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/logo-abm.png') }}">


    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>

    {{-- Sweatalert 2 for notification with cdn --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- select2 for multiple select option --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- select2 for multiple select option --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>

<body x-data="{ page: 'comingSoon', 'loaded': true, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }">

    <!-- ===== Page Wrapper Start ===== -->
    <div class="relative p-6 bg-white z-1  sm:p-0">
        <div class="relative flex flex-col justify-center w-full h-screen  sm:p-0 lg:flex-row">

            <div class="relative items-center hidden w-full h-full bg-brand-950  lg:grid lg:w-1/2">
                <div class="flex items-center justify-center z-1">
                    <!-- ===== Common Grid Shape Start ===== -->
                    {{-- <include src="./partials/common-grid-shape.html"></include> --}}
                    @include('partials.common-grid-shape')
                    <div class="flex flex-col items-center max-w-xs">
                        <a href="https://asadreamland.com/" class="block mb-4 bg-white rounded-full">
                            <img src="{{ asset('images/logo/abm.PNG') }}" alt="Logo" />
                        </a>

                        <figure class="max-w-screen-md mx-auto text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-400 dark:text-gray-600" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 14">
                                <path
                                    d="M6 0H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v1a3 3 0 0 1-3 3H2a1 1 0 0 0 0 2h1a5.006 5.006 0 0 0 5-5V2a2 2 0 0 0-2-2Zm10 0h-4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v1a3 3 0 0 1-3 3h-1a1 1 0 0 0 0 2h1a5.006 5.006 0 0 0 5-5V2a2 2 0 0 0-2-2Z" />
                            </svg>
                            <blockquote>
                                <p class="text-2xl italic font-medium text-white">" Grow Big With ABM "</p>
                            </blockquote>
                            <figcaption class="flex items-center justify-center mt-6 space-x-3 rtl:space-x-reverse">
                                <img class="w-6 h-6 rounded-full " src="{{ asset('images/logo/logo-abm.png') }}"
                                    alt="profile picture">
                                <div
                                    class="flex items-center divide-x-2 rtl:divide-x-reverse divide-gray-500 dark:divide-gray-700">
                                    <cite class="pe-3 font-medium text-white">ASA GROUP</cite>
                                </div>
                            </figcaption>
                        </figure>


                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="flex flex-col flex-1 w-full lg:w-1/2">
                <div class="w-full max-w-md pt-10 mx-auto">
                    <a href="#"
                        class="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 ">
                        <svg class="stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 20 20" fill="none">
                            <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ today()->translatedFormat('l, d F Y') }}
                    </a>
                </div>
                <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                    <div>
                        <div class="mb-5 sm:mb-8">
                            <h1 class="mb-2 font-semibold text-gray-800 text-title-sm  sm:text-title-md">
                                Sign In
                            </h1>
                            <p class="text-sm text-gray-500 ">
                                Enter your username and password to sign in!
                            </p>
                        </div>

                        {{-- Alert Error Validasi --}}
                        @if ($errors->any())
                            <div class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800"
                                role="alert">
                                <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                                </svg>
                                <span class="sr-only">Info</span>
                                @foreach ($errors->all() as $error)
                                    <div>
                                        {{ $error }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div>
                            <form action="{{ route('login.post') }}" method="POST">
                                @csrf

                                <div class="space-y-5">
                                    <!-- username -->
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 ">
                                            Username<span class="text-error-500">*</span>
                                        </label>
                                        <input type="username" id="username" name="username" placeholder="user 123"
                                            required
                                            class=" h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 " />
                                    </div>
                                    <!-- Password -->
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 ">
                                            Password<span class="text-error-500">*</span>
                                        </label>
                                        <div x-data="{ showPassword: false }" class="relative">
                                            <input :type="showPassword ? 'text' : 'password'" required
                                                placeholder="Enter your password" name="password"
                                                class=" h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 " />
                                            <span @click="showPassword = !showPassword"
                                                class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 ">
                                                <svg x-show="!showPassword" class="fill-current" width="20"
                                                    height="20" viewBox="0 0 20 20" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z"
                                                        fill="#98A2B3" />
                                                </svg>
                                                <svg x-show="showPassword" class="fill-current" width="20"
                                                    height="20" viewBox="0 0 20 20" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0064 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z"
                                                        fill="#98A2B3" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Button -->
                                    <div>
                                        <button
                                            class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                                            Sign In
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ===== Page Wrapper End ===== -->
</body>

</html>
