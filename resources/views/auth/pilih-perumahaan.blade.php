<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {{-- csrf token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ 'Asa Group' }}</title>
    @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.js'])


    <link rel="icon" href="{{ asset('images/logo/logo-abm.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/logo/logo-abm.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/logo-abm.png') }}">

<body>
    <div class="min-h-screen flex justify-center items-center bg-gray-100 px-4">
        <div class="grid w-full max-w-4xl gap-6 grid-cols-1 md:grid-cols-2">
            @foreach ($perumahaan as $item)
                <form action="{{ route('perumahaanSession.store') }}" method="POST"
                    class="border rounded-lg shadow-lg p-6 hover:shadow-xl transition
                             bg-white border-gray-200 hover:bg-gray-50 border-l-6
                             {{ $item->slug === 'asa-dreamland' ? 'border-l-blue-500' : 'border-l-yellow-500' }}">
                    @csrf
                    <input type="hidden" name="perumahaan_id" value="{{ $item->id }}">

                    {{-- Button menutupi seluruh card, tampil seperti link --}}
                    <button type="submit" class="text-left w-full h-full">
                        <h2
                            class="text-xl font-semibold mb-2
                                   {{ $item->slug === 'asa-dreamland' ? 'text-blue-500' : 'text-yellow-500' }}">
                            {{ $item->nama_perumahaan }}
                        </h2>
                        <p class="text-gray-600">
                            {{ $item->alamat }}
                        </p>
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</body>




</html>
