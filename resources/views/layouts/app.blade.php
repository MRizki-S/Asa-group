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

<body x-cloak x-data="{
    page: '@yield('pageActive')',
    {{-- loaded: true, --}}
    darkMode: false,
    stickyMenu: false,
    sidebarToggle: false,
    scrollTop: false
}" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode'));
$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))" :class="{ 'dark bg-gray-900': darkMode === true }">
    <!-- Preloader -->
    {{-- @include('partials.preloader') --}}

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('partials.sidebar')

        <!-- Content -->
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            {{-- @include('partials.overlay') --}}
            @include('partials.header')

            <main>
                <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>




    {{-- function format rupiah --}}
    <script>
        function rupiahInput(initial = '') {
            return {
                display: initial ? formatRupiah(initial) : '',
                value: initial.replace(/\D/g, ''),
                onInput(e) {
                    this.value = e.target.value.replace(/\D/g, '');
                    this.display = this.value ? formatRupiah(this.value) : '';
                }
            }
        }

        function formatRupiah(num) {
            if (!num) return '';
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>



    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('success')),
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false,
                backdrop: `
                rgba(0,0,0,0.4)
                left top
                no-repeat
            `
            })
        </script>
    @elseif (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops…',
                text: @json(session('error')),
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            })
        </script>
    @endif



</body>

</html>
