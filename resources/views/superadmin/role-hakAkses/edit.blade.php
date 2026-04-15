@extends('layouts.app')

@section('pageActive', 'RoleHakAkses')

@section('content')
    {{-- select 2  --}}
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css">

    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6">

        <!-- Breadcrumb -->
        <div x-data="{ pageName: 'RoleHakAkses' }">
            @include('partials.breadcrumb')
        </div>

        <!-- Alert Error Validasi -->
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3 mt-[2px]" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <div>
                    <span class="font-medium">Terjadi kesalahan validasi:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('superadmin.roleHakAkses.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Penamaan Role --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 shadow-sm">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3
                        class="text-base font-semibold text-gray-800 dark:text-white/90 mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        Informasi Role
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Role -->
                        <div>
                            <label for="role_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Nama Role <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="role_name" name="role_name" required
                                value="{{ old('role_name', $role->name) }}" placeholder="Contoh: Manager, Staff, Admin"
                                class="w-full bg-gray-50 border text-gray-900 text-sm rounded-lg p-2.5
                               dark:bg-gray-700 dark:text-white
                               @error('role_name') border-red-500 @else border-gray-300 @enderror">
                            @error('role_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hak Akses --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] mb-6 shadow-sm">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Hak Akses (Permissions)
                    </h3>

                    <div class="space-y-8">
                        @foreach ($groupedPermissions as $category => $modules)
                            <div x-data="{ 
                                    allChecked: false,
                                    toggleAll() {
                                        this.allChecked = !this.allChecked;
                                        const checkboxes = $el.querySelectorAll('.perm-checkbox');
                                        checkboxes.forEach(cb => cb.checked = this.allChecked);
                                    }
                                }" 
                                class="border border-gray-100 dark:border-gray-800 rounded-xl p-4 bg-gray-50/50 dark:bg-gray-800/20">
                                
                                <div class="flex items-center justify-between mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                                    <h4 class="text-sm font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400">
                                        {{ str_replace('-', ' ', $category) }}
                                    </h4>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" @click="toggleAll" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out rounded border-gray-300">
                                        <span class="ml-2 text-xs font-medium text-gray-600 dark:text-gray-400 italic">Pilih Semua {{ ucfirst($category) }}</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                    @foreach ($modules as $module => $subModules)
                                        <div class="space-y-4">
                                            <h5 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                                <div class="w-2 h-2 rounded bg-blue-600"></div>
                                                {{ strtoupper(str_replace('-', ' ', $module)) }}
                                            </h5>
                                            
                                            <div class="space-y-4 pl-4 border-l-2 border-gray-100 dark:border-gray-800">
                                                @foreach ($subModules as $subModule => $perms)
                                                    <div class="space-y-2">
                                                        @if ($subModule !== 'default')
                                                            <h6 class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                                                                {{ str_replace('-', ' ', $subModule) }}
                                                            </h6>
                                                        @endif
                                                        
                                                        <div class="flex flex-col gap-2">
                                                            @foreach ($perms as $perm)
                                                                @php
                                                                    $parts = explode('.', $perm->name);
                                                                    $action = end($parts);
                                                                    $label = ucfirst(str_replace('-', ' ', $action));
                                                                @endphp
                                                                <label class="inline-flex items-center group cursor-pointer">
                                                                    <div class="relative flex items-center">
                                                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                                            {{ in_array($perm->id, $rolePermissions) ? 'checked' : '' }}
                                                                            class="perm-checkbox form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out rounded border-gray-300 focus:ring-blue-500">
                                                                    </div>
                                                                    <span class="ml-2.5 text-sm text-gray-600 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-300 transition-colors">
                                                                        {{ $label }}
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>
            </div>


            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-2">
                <a href="{{ route('superadmin.roleHakAkses.index') }}"
                    class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300
                       dark:text-white dark:bg-gray-700 dark:hover:bg-gray-600">
                    Batal
                </a>
                @can('superadmin.role.update')
                <button type="submit"
                    class="px-8 py-2.5 text-sm font-medium text-white rounded-lg bg-blue-600 hover:bg-blue-700
                       focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800 shadow-md">
                    Update Role
                </button>
                @endcan
            </div>
        </form>
    </div>
@endsection
