<aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[250px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0">
    <!-- SIDEBAR HEADER -->
    <div :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="flex items-center gap-2 pt-8 sidebar-header pb-7">
        <a href="" class="flex items-center gap-2">
            <!-- Logo -->
            <span class="logo flex justify-cent" :class="sidebarToggle ? 'hidden' : ''">
                <img src="{{ asset('images/logo/logo-abm.png') }}" alt="Logo" class="w-14 h-auto" />
            </span>
            <!-- Tulisan di samping logo -->
            <span class="text-xl font-bold" :class="sidebarToggle ? 'hidden' : ''">
                <span class="text-blue-500">ABM</span>
                <span class="text-black xc dark:text-white">Group</span>
            </span>
        </a>
    </div>

    <!-- SIDEBAR HEADER -->
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <!-- Sidebar Menu -->
        <nav x-data="{ selected: $persist('Dashboard') }">

            <!-- Menu Group - Dashboard -->
            @canany(['dashboard.marketing.read', 'dashboard.keuangan.read', 'dashboard.produksi.read',
                'dashboard.gudang.read'])
                <div>
                    <ul class="flex flex-col gap-4 mb-6">
                        <li>
                            {{-- dahboard title --}}
                            <a href="/#" @click.prevent="selected = (selected === 'Dashboard' ? '':'Dashboard')"
                                class="menu-item group"
                                :class="(selected === 'Dashboard') || (page === 'Dashboard-Marketing' ||
                                    page === 'marketing' || page === 'Keuangan' || page === 'Produksi' ||
                                    page === 'Gudang') ?
                                'menu-item-active' : 'menu-item-inactive'">
                                <svg :class="(selected === 'Dashboard') || (page === 'Marketing' ||
                                    page === 'marketing' || page === 'Keuangan' || page === 'Produksi' ||
                                    page === 'Gudang') ?
                                'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                                        fill="" />
                                </svg>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Dashboard
                                </span>

                                <svg class="menu-item-arrow"
                                    :class="[(selected === 'Dashboard') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive',
                                        sidebarToggle ? 'lg:hidden' : ''
                                    ]"
                                    width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>

                            <!-- Dropdown Menu Start -->
                            <div class="overflow-hidden transform translate"
                                :class="(selected === 'Dashboard') ? 'block' : 'hidden'">
                                <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                    class="flex flex-col mt-2 menu-dropdown pl-9">

                                    {{-- Link menu Dashboard - Marketing --}}
                                    @can('dashboard.marketing.read')
                                        <li>
                                            <a href="{{ route('dashboard.marketing.index') }}" class="menu-dropdown-item group"
                                                :class="page === 'Dashboard-Marketing' ? 'menu-dropdown-item-active' :
                                                    'menu-dropdown-item-inactive'">
                                                Marketing Properti
                                            </a>
                                        </li>
                                    @endcan
                                    {{-- Link menu Dashboard - Marketing --}}

                                    {{-- Link menu Dashboard - Keuangan --}}
                                    @can('dashboard.keuangan.read')
                                        <li>
                                            <a href="index.html" class="menu-dropdown-item group"
                                                :class="page === 'Keuangan' ? 'menu-dropdown-item-active' :
                                                    'menu-dropdown-item-inactive'">
                                                Keuangan
                                            </a>
                                        </li>
                                    @endcan
                                    {{-- Link menu Dashboard - Keuangan --}}

                                    {{-- Link menu Dashboard - Produksi --}}
                                    @can('dashboard.produksi.read')
                                        <li>
                                            <a href="index.html" class="menu-dropdown-item group"
                                                :class="page === 'Produksi' ? 'menu-dropdown-item-active' :
                                                    'menu-dropdown-item-inactive'">
                                                Produksi
                                            </a>
                                        </li>
                                    @endcan
                                    {{-- Link menu Dashboard - Produksi --}}

                                    {{-- Link menu Dashboard - Gudang > Stock Gudang --}}
                                    <li>
                                        <a href="index.html" class="menu-dropdown-item group"
                                            :class="page === 'Stock Gudang' ? 'menu-dropdown-item-active' :
                                                'menu-dropdown-item-inactive'">
                                            Stock Gudang
                                        </a>
                                    </li>
                                    {{-- Link menu Dashboard - Gudang > Stock Gudang --}}
                                </ul>
                            </div>
                            <!-- Dropdown Menu End -->
                        </li>
                        <!-- Menu Item Dashboard -->
                    </ul>
                </div>
            @endcanany
            <!-- Menu Group - Dashboard -->

            {{-- Menu Etalase --}}
            @php
                $etlalaseMenu = [
                    'etalase.tahap.read',
                    'etalase.type-unit.read',
                    'etalase.kualifikasi-blok.read',
                    'etalase.blok.read',
                    'etalase.unit.read',
                    'etalase.perubahaan-harga.type-unit.read',
                    'etalase.perubahaan-harga.tahap-kualifikasi.read',
                ];
            @endphp
            @if (auth()->user()->canAny($etlalaseMenu))
                <!-- Menu Group - Etalase -->
                <div>
                    <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                            Etalase
                        </span>

                        <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                            class="mx-auto fill-current menu-group-icon" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                fill="" />
                        </svg>
                    </h3>

                    <ul class="flex flex-col gap-2 mb-6">
                        <!-- Menu Item Perumahaan -->
                        @can('etalase.tahap.read')
                            <li>
                                <a href="/etalase/perumahaan"
                                    @click="selected = (selected === 'Perumahaan' ? '':'Perumahaan')"
                                    class="menu-item group"
                                    :class="(selected === 'Perumahaan') && (page === 'Perumahaan') ? 'menu-item-active' :
                                    'menu-item-inactive'">

                                    <svg :class="(selected === 'Perumahaan') && (page === 'Perumahaan') ? 'menu-item-icon-active' :
                                    'menu-item-icon-inactive'"
                                        width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                        class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                                    </svg>


                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Perumahaan
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Perumahaan -->

                        <!-- Menu Item Tipe Unit -->
                        @can('etalase.type-unit.read')
                            <li>
                                <a href="/etalase/tipe-unit" @click="selected = (selected === 'TipeUnit' ? '':'TipeUnit')"
                                    class="menu-item group"
                                    :class="(selected === 'TipeUnit') && (page === 'TipeUnit') ? 'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg :class="(selected === 'TipeUnit') && (page === 'TipeUnit') ? 'menu-item-icon-active' :
                                    'menu-item-icon-inactive'"
                                        width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                                    </svg>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Tipe Unit
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Tipe Unit -->

                        <!-- Menu Item Kualifikasi Blok -->
                        @can('etalase.kualifikasi-blok.read')
                            <li>
                                <a href="/etalase/kualifikasi-blok"
                                    @click="selected = (selected === 'KualifikasiBlok' ? '':'KualifikasiBlok')"
                                    class="menu-item group"
                                    :class="(selected === 'KualifikasiBlok') && (page === 'KualifikasiBlok') ?
                                    'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg :class="(selected === 'KualifikasiBlok') && (page === 'KualifikasiBlok') ?
                                    'men    u-item-icon-active' :
                                    'menu-item-icon-inactive'"
                                        width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                                    </svg>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Kualifikasi Blok
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Kualifikasi Blok -->

                        <!-- Menu Item Blok - Unit -->
                        @canany(['etalase.blok.read', 'etalase.unit.read'])
                            <li>
                                <a href="#" @click.prevent="selected = (selected === 'blokUnit' ? '':'blokUnit')"
                                    class="menu-item group"
                                    :class="(selected === 'blokUnit') || (page === 'blokLayout' || page === 'formLayout' ||
                                        page === 'problokLayout' || page === 'proFormLayout') ? 'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg :class="(selected === 'blokUnit') || (page === 'blokLayout' || page === 'formLayout' ||
                                        page === 'problokLayout' || page === 'proFormLayout') ?
                                    'menu-item-icon-active' :
                                    'menu-item-icon-inactive'"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H18.5001C19.7427 20.75 20.7501 19.7426 20.7501 18.5V5.5C20.7501 4.25736 19.7427 3.25 18.5001 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H18.5001C18.9143 4.75 19.2501 5.08579 19.2501 5.5V18.5C19.2501 18.9142 18.9143 19.25 18.5001 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V5.5ZM6.25005 9.7143C6.25005 9.30008 6.58583 8.9643 7.00005 8.9643L17 8.96429C17.4143 8.96429 17.75 9.30008 17.75 9.71429C17.75 10.1285 17.4143 10.4643 17 10.4643L7.00005 10.4643C6.58583 10.4643 6.25005 10.1285 6.25005 9.7143ZM6.25005 14.2857C6.25005 13.8715 6.58583 13.5357 7.00005 13.5357H17C17.4143 13.5357 17.75 13.8715 17.75 14.2857C17.75 14.6999 17.4143 15.0357 17 15.0357H7.00005C6.58583 15.0357 6.25005 14.6999 6.25005 14.2857Z"
                                            fill="" />
                                    </svg>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Blok - Unit
                                    </span>

                                    <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                        :class="[(selected === 'blokUnit') ? 'menu-item-arrow-active' :
                                            'menu-item-arrow-inactive',
                                            sidebarToggle ? 'lg:hidden' : ''
                                        ]"
                                        width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>

                                <!-- Dropdown Menu Start -->
                                <div class="overflow-hidden transform translate"
                                    :class="(selected === 'blokUnit') ? 'block' : 'hidden'">
                                    <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                        class="flex flex-col mt-2 menu-dropdown pl-9">
                                        <!-- Blok Menu -->
                                        @can('etalase.blok.read')
                                            <li>
                                                <a href="{{ route('blok.index') }}" class="menu-dropdown-item group"
                                                    :class="page === 'blokLayout' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        fill="none" viewBox="0 0 24 24">
                                                        <path stroke="currentColor" stroke-linejoin="round"
                                                            stroke-width="1.2"
                                                            d="M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm16 14a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2ZM4 13a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6Zm16-2a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6Z" />
                                                    </svg>
                                                    Blok
                                                </a>
                                            </li>
                                        @endcan
                                        @can('etalase.unit.read')
                                            <!-- Unit Menu -->
                                            <li>
                                                <a href="{{ route('unit.indexGlobal') }}" class="menu-dropdown-item group"
                                                    :class="page === 'unitLayout' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        fill="none" viewBox="0 0 24 24">
                                                        <path stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="1.4"
                                                            d="m7.4285 11 5-6 5 6m-10 0h-3v8h16v-8h-3m-10 0H3.42851l3-4h4.33739l-3.3374 4Zm10 0V7.5m-3 5.5c0 1.1046-.8954 2-2 2s-2-.8954-2-2 .8954-2 2-2 2 .8954 2 2Zm3-8v3h4V5h-4Z" />
                                                    </svg>
                                                    Unit
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                                <!-- Dropdown Menu End -->
                            </li>
                        @endcanany
                        <!-- Menu Item blokUnit -->

                        <!-- Menu Item Pengajuan Perubahaan Harga -->
                        @canany(['etalase.perubahaan-harga.type-unit.read',
                            'etalase.perubahaan-harga.tahap-kualifikasi.read'])
                            <li>
                                <a href="#"
                                    @click.prevent="selected = (selected === 'PengajuanHarga' ? '':'PengajuanHarga')"
                                    class="menu-item group"
                                    :class="(selected === 'PengajuanHarga') || (page === 'PerubahaanHargaTipeUnit' ||
                                        page === 'PerubahaanHargaTahapKualifikasiBlok') ? 'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg :class="(selected === 'PengajuanHarga') || (page === 'PerubahaanHargaTipeUnit' ||
                                        page === 'PerubahaanHargaTahapKualifikasiBlok'
                                    ) ? 'menu-item-icon-active' :
                                    'menu-item-icon-inactive'"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H18.5001C19.7427 20.75 20.7501 19.7426 20.7501 18.5V5.5C20.7501 4.25736 19.7427 3.25 18.5001 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H18.5001C18.9143 4.75 19.2501 5.08579 19.2501 5.5V18.5C19.2501 18.9142 18.9143 19.25 18.5001 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V5.5ZM6.25005 9.7143C6.25005 9.30008 6.58583 8.9643 7.00005 8.9643L17 8.96429C17.4143 8.96429 17.75 9.30008 17.75 9.71429C17.75 10.1285 17.4143 10.4643 17 10.4643L7.00005 10.4643C6.58583 10.4643 6.25005 10.1285 6.25005 9.7143ZM6.25005 14.2857C6.25005 13.8715 6.58583 13.5357 7.00005 13.5357H17C17.4143 13.5357 17.75 13.8715 17.75 14.2857C17.75 14.6999 17.4143 15.0357 17 15.0357H7.00005C6.58583 15.0357 6.25005 14.6999 6.25005 14.2857Z"
                                            fill="" />
                                    </svg>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Perubahaan Harga
                                    </span>

                                    <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                        :class="[(selected === 'PengajuanHarga') ? 'menu-item-arrow-active' :
                                            'menu-item-arrow-inactive',
                                            sidebarToggle ? 'lg:hidden' : ''
                                        ]"
                                        width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>

                                <!-- Dropdown Menu Start -->
                                <div class="overflow-hidden transform translate"
                                    :class="(selected === 'PengajuanHarga') ? 'block' : 'hidden'">
                                    <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                        class="flex flex-col mt-2 menu-dropdown pl-9">
                                        <!-- Perubahaan harga tipe unit -->
                                        @can('etalase.perubahaan-harga.type-unit.read')
                                            <li>
                                                <a href="{{ route('perubahan-harga.tipe-unit.index') }}"
                                                    class="menu-dropdown-item group"
                                                    :class="page === 'PerubahanHargaTipeUnit' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" width="24"
                                                        height="24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                                                    </svg>
                                                    Harga Tipe Unit
                                                </a>
                                            </li>
                                        @endcan

                                        <!-- Perubahaan Harga tipe - Kualifikasi -->
                                        @can('etalase.perubahaan-harga.tahap-kualifikasi.read')
                                            <li>
                                                <a href="{{ route('under-development') }}" class="menu-dropdown-item group"
                                                    :class="page === 'PerubahaanHargaTahapKualifikasiBlok' ?
                                                        'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" width="24"
                                                        height="24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                                                    </svg>
                                                    Harga Tahap - Kualifikasi Blok
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                                <!-- Dropdown Menu End -->
                            </li>
                        @endcanany
                        <!-- Menu Item Pengajuan Perubahaan Harga-->

                    </ul>
                </div>
                <!-- Menu Group - Etalase -->
            @endif

            {{-- Menu Marketing --}}
            @php
                $marketingMenu = [
                    'Customer' => 'marketing.customer.read',
                    'Pemesanan Unit' => 'marketing.pemesanan-unit.read',
                    'Kelola Pemesanan' => 'marketing.kelola-pemesanan.read',
                    'Pengajuan Pemesanan' => 'marketing.pengajuan-pemesanan.read',
                    'Pengajuan Pembatalan' => 'marketing.pengajuan-pembatalan.read',
                    'Adendum' => 'marketing.adendum.pengajuan-adendum.read',
                    'List Adendum' => 'marketing.list-adendum.read',
                    'Setting PPJB' => 'marketing.setting-ppjb.read',
                ];
            @endphp
            @if (auth()->user()->canAny(array_values($marketingMenu)))
                <!-- Marketing -  Group -->
                <div>
                    <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                            Marketing
                        </span>

                        <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                            class="mx-auto fill-current menu-group-icon" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                fill="" />
                        </svg>
                    </h3>

                    <ul class="flex flex-col gap-2 mb-6">
                        <!-- Menu Item Akun User -->
                        @can('marketing.customer.read')
                            <li>
                                <a href="{{ route('marketing.akunUser.index') }}"
                                    @click="selected = (selected === 'AkunUser' ? '':'AkunUser')" class="menu-item group"
                                    :class="(selected === 'AkunUser') && (page === 'AkunUser') ? 'menu-item-active' :
                                    'menu-item-inactive'">

                                    <svg :class="(selected === 'AkunUser') && (page === 'AkunUser') ? 'menu-item-icon-active' :
                                    ''"
                                        width="24" height="24" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path fill-rule="evenodd"
                                            d="M7 2a2 2 0 0 0-2 2v1a1 1 0 0 0 0 2v1a1 1 0 0 0 0 2v1a1 1 0 1 0 0 2v1a1 1 0 1 0 0 2v1a1 1 0 1 0 0 2v1a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H7Zm3 8a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm-1 7a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3 1 1 0 0 1-1 1h-6a1 1 0 0 1-1-1Z"
                                            clip-rule="evenodd" />
                                    </svg>


                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        User Booking
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Akun User -->


                        <!-- Menu Item Pemesanan Unit -->
                        @can('marketing.pemesanan-unit.read')
                            <li>
                                <a href="{{ route('marketing.pemesananUnit.index') }}"
                                    @click="selected = (selected === 'PemesananUnit' ? '':'PemesananUnit')"
                                    class="menu-item group"
                                    :class="(selected === 'PemesananUnit') && (page === 'PemesananUnit') ?
                                    'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg :class="(selected === 'PemesananUnit') && (page === 'PemesananUnit') ?
                                    'menu-item-icon-active' :
                                    ''"
                                        width="24" height="24" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-notebook-pen-icon lucide-notebook-pen">
                                        <path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4" />
                                        <path d="M2 6h4" />
                                        <path d="M2 10h4" />
                                        <path d="M2 14h4" />
                                        <path d="M2 18h4" />
                                        <path
                                            d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z" />
                                    </svg>
                                    </svg>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Pemesanan Unit
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Pemesanan Unit -->

                        <!-- Menu Item Kelola Pemesanan -->
                        @can('marketing.kelola-pemesanan.read')
                            <li>
                                <a href="/marketing/manage-pemesanan"
                                    @click="selected = (selected === 'ManagePemesanan' ? '':'ManagePemesanan')"
                                    class="menu-item group"
                                    :class="(selected === 'ManagePemesanan') && (page === 'ManagePemesanan') ?
                                    'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg :class="(selected === 'ManagePemesanan') && (page === 'ManagePemesanan') ?
                                    'menu-item-icon-active' :
                                    'menu-item-icon-inactive'"
                                        width="26" height="26" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.4"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-folder-kanban-icon lucide-folder-kanban">
                                        <path
                                            d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z" />
                                        <path d="M8 10v4" />
                                        <path d="M12 10v2" />
                                        <path d="M16 10v6" />
                                    </svg>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Kelola Pemesanan
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Kelola Pemesanan -->

                        <!-- Menu Item Pengajuan -->
                        @canany(['marketing.pengajuan-pemesanan.read', 'marketing.pengajuan-pembatalan.read'])
                            <li>
                                <a href="#" @click.prevent="selected = (selected === 'Pengajuan' ? '':'Pengajuan')"
                                    class="menu-item group"
                                    :class="(selected === 'Pengajuan') || (page === 'PengajuanPemesanan' ||
                                        page === 'PengajuanPembatalan' ||
                                        page === 'pieChart') ? 'menu-item-active' : 'menu-item-inactive'">
                                    <svg :class="(selected === 'Pengajuan') || (page === 'PengajuanPemesanan' ||
                                        page === 'PengajuanPembatalan' ||
                                        page === 'pieChart') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                    </svg>


                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Pengajuan
                                    </span>

                                    <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                        :class="[(selected === 'Pengajuan') ? 'menu-item-arrow-active' :
                                            'menu-item-arrow-inactive',
                                            sidebarToggle ? 'lg:hidden' : ''
                                        ]"
                                        width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>

                                <!-- Dropdown Menu Start -->
                                <div class="overflow-hidden transform translate"
                                    :class="(selected === 'Pengajuan') ? 'block' : 'hidden'">
                                    <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                        class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">

                                        {{-- Pengajuan Pemesanan --}}
                                        @can('marketing.pengajuan-pemesanan.read')
                                            <li>
                                                <a href="/marketing/pengajuan-pemesanan" class="menu-dropdown-item group"
                                                    :class="page === 'PengajuanPemesanan' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    Pengajuan Pemesanan
                                                </a>
                                            </li>
                                        @endcan
                                        {{-- Pengajuan Pembatalan --}}
                                        @can('marketing.pengajuan-pembatalan.read')
                                            <li>
                                                <a href="/marketing/pengajuan-pembatalan" class="menu-dropdown-item group"
                                                    :class="page === 'PengajuanPembatalan' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    Pengajuan Pembatalan
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                                <!-- Dropdown Menu End -->
                            </li>
                        @endcanany
                        <!-- Menu Item Pengajuan -->

                        <!-- Menu Item Adendum -->
                        @canany(['marketing.adendum.pengajuan-adendum.read', 'marketing.adendum.list-adendum.read'])
                            <li>
                                <a href="#" @click.prevent="selected = (selected === 'Adendum' ? '':'Adendum')"
                                    class="menu-item group"
                                    :class="(selected === 'Adendum') || (page === 'BuatAdendum' ||
                                        page === 'ListAdendum') ? 'menu-item-active' : 'menu-item-inactive'">
                                    <svg :class="(selected === 'Adendum') || (page === 'BuatAdendum' || page === 'ListAdendum') ?
                                    'menu-item-icon-active' :
                                    'menu-item-icon'"
                                        class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        aria-hidden="true" role="img">
                                        <!-- bentuk berkas dengan lipatan pojok -->
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v6h6" />
                                        <!-- optional: garis isi dokumen -->
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17h6" />
                                    </svg>


                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Adendum
                                    </span>

                                    <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                        :class="[(selected === 'Adendum') || (page === 'BuatAdendum' ||
                                                page === 'ListAdendum') ? 'menu-item-arrow-active' :
                                            'menu-item-arrow-inactive',
                                            sidebarToggle ? 'lg:hidden' : ''
                                        ]"
                                        width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>

                                <!-- Dropdown Menu Start -->
                                <div class="overflow-hidden transform translate"
                                    :class="(selected === 'Adendum') || (page === 'BuatAdendum' || page === 'ListAdendum') ?
                                    'block' : 'hidden'"
                                    warehouse-1>
                                    <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                        class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">

                                        {{-- Buat Adendum --}}
                                        @can('marketing.adendum.pengajuan-adendum.read')
                                            <li>
                                                <a href="{{ route('marketing.adendum.index') }}"
                                                    class="menu-dropdown-item group"
                                                    :class="page === 'BuatAdendum' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    Buat Adendum
                                                </a>
                                            </li>
                                        @endcan

                                        {{-- List Adendum --}}
                                        @can('marketing.adendum.list-adendum.read')
                                            <li>
                                                <a href="{{ route('under-development') }}" {{-- {{ route('marketing.adendum.list') }} --}}
                                                    class="menu-dropdown-item group"
                                                    :class="page === 'ListAdendum' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    List Adendum
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                                <!-- Dropdown Menu End -->
                            </li>
                        @endcanany
                        <!-- Menu Item Adendum -->

                        <!-- Menu Item Setting PPJB -->
                        @can('marketing.setting-ppjb.read')
                            <li>
                                <a href="/marketing/setting"
                                    @click="selected = (selected === 'SettingPPJB' ? 'SettingPPJB':'SettingPPJB')"
                                    class="menu-item group"
                                    :class="(selected === 'SettingPPJB') && (page === 'SettingPPJB') ? 'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg :class="(selected === 'SettingPPJB') && (page === 'SettingPPJB') ?
                                    'menu-item-icon-active' :
                                    ''"
                                        width="24" height="24" xmlns="http://www.w3.org/2000/svg" width="20"
                                        height="20" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </xmlns=>

                                        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                            Setting PPJB
                                        </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Setting PPJB -->

                        <!-- Menu Item Master Agent -->
                        @canany(['master-agen.agen.read', 'master-agen.fee-agen.read'])
                            <li>
                                <a href="#" @click.prevent="selected = (selected === 'MasterAgen' ? '':'MasterAgen')"
                                    class="menu-item group"
                                    :class="(selected === 'MasterAgen') || (page === 'Agen' ||
                                        page === 'FeeAgen') ? 'menu-item-active' : 'menu-item-inactive'">
                                  <svg :class="(selected === 'MasterAgen') || (page === 'Agen' || page === 'FeeAgen') ?
                                        'menu-item-icon-active' :
                                        'menu-item-icon'"
                                    class="w-5 h-5 flex-shrink-0"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    aria-hidden="true"
                                    role="img">
                                    <!-- kepala tengah -->
                                    <circle cx="12" cy="8" r="3" stroke-linecap="round" stroke-linejoin="round" />
                                    <!-- badan tengah -->
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6 19c0-3 3-5 6-5s6 2 6 5" />
                                    <!-- kepala kiri -->
                                    <circle cx="5" cy="10" r="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <!-- kepala kanan -->
                                    <circle cx="19" cy="10" r="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <!-- badan kiri -->
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2 19c0-2 2-3.5 4-4" />
                                    <!-- badan kanan -->
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M22 19c0-2-2-3.5-4-4" />
                                </svg>



                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Master Agen
                                    </span>

                                    <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                        :class="[(selected === 'MasterAgen') || (page === 'Agen' ||
                                                page === 'FeeAgen') ? 'menu-item-arrow-active' :
                                            'menu-item-arrow-inactive',
                                            sidebarToggle ? 'lg:hidden' : ''
                                        ]"
                                        width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>

                                <!-- Dropdown Menu Start -->
                                <div class="overflow-hidden transform translate"
                                    :class="(selected === 'MasterAgen') || (page === 'Agen' || page === 'FeeAgen') ?
                                    'block' : 'hidden'"warehouse-1>
                                    <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                        class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">

                                        {{-- Agent --}}
                                        @can('master-agen.agen.read')
                                            <li>
                                                <a href="{{ route('marketing.agen.index') }}"
                                                    class="menu-dropdown-item group"
                                                    :class="page === 'Agen' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    Agen
                                                </a>
                                            </li>
                                        @endcan

                                        {{-- Fee Agent --}}
                                        @can('master-agen.fee-agen.read')
                                            <li>
                                            <a href="{{ route('marketing.feeAgen.index') }}"
                                                    class="menu-dropdown-item group"
                                                    :class="page === 'FeeAgen' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    Fee Agen
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                                <!-- Dropdown Menu End -->
                            </li>
                        @endcanany
                        <!-- Menu Item Master Agent -->

                        <!-- Menu Item Target Marketing -->
                        @canany(['target-marketing.target-penjualan.read', 'target-marketing.anggaran-promosi.read'])
                            <li>
                                <a href="#" @click.prevent="selected = (selected === 'TargetMarketing' ? '':'TargetMarketing')"
                                    class="menu-item group"
                                    :class="(selected === 'TargetMarketing') || (page === 'TargetPenjualan' ||
                                        page === 'AnggaranPromosi') ? 'menu-item-active' : 'menu-item-inactive'">
                                  <svg :class="(selected === 'TargetMarketing') || (page === 'TargetPenjualan' || page === 'AnggaranPromosi') ?
                                        'menu-item-icon-active' :
                                        'menu-item-icon'"
                                    class="w-5 h-5 flex-shrink-0"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    aria-hidden="true"
                                    role="img">
                                    <!-- kepala tengah -->
                                    <circle cx="12" cy="8" r="3" stroke-linecap="round" stroke-linejoin="round" />
                                    <!-- badan tengah -->
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6 19c0-3 3-5 6-5s6 2 6 5" />
                                    <!-- kepala kiri -->
                                    <circle cx="5" cy="10" r="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <!-- kepala kanan -->
                                    <circle cx="19" cy="10" r="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <!-- badan kiri -->
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2 19c0-2 2-3.5 4-4" />
                                    <!-- badan kanan -->
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M22 19c0-2-2-3.5-4-4" />
                                  </svg>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Target Marketing
                                    </span>

                                    <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                        :class="[(selected === 'TargetMarketing') || (page === 'TargetPenjualan' ||
                                                page === 'AnggaranPromosi') ? 'menu-item-arrow-active' :
                                             'menu-item-arrow-inactive',
                                            sidebarToggle ? 'lg:hidden' : ''
                                        ]"
                                        width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>

                                <!-- Dropdown Menu Start -->
                                <div class="overflow-hidden transform translate"
                                    :class="(selected === 'TargetMarketing') || (page === 'TargetPenjualan' || page === 'AnggaranPromosi') ?
                                    'block' : 'hidden'"warehouse-1>
                                    <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                        class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">

                                        {{-- Target Penjualan --}}
                                        @can('target-marketing.target-penjualan.read')
                                            <li>
                                                <a href="{{ route('marketing.target-penjualan.index') }}"
                                                    class="menu-dropdown-item group"
                                                    :class="page === 'TargetPenjualan' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    Target Penjualan
                                                </a>
                                            </li>
                                        @endcan

                                        {{-- Anggaran Promosi --}}
                                        @can('target-marketing.anggaran-promosi.read')
                                            <li>
                                                <a href="{{ route('marketing.anggaran-promosi.index') }}"
                                                        class="menu-dropdown-item group"
                                                        :class="page === 'AnggaranPromosi' ? 'menu-dropdown-item-active' :
                                                            'menu-dropdown-item-inactive'">
                                                    Anggaran Promosi
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                                <!-- Dropdown Menu End -->
                            </li>
                        @endcanany
                        <!-- Menu Item Target Marketing -->
<!--  -->
                    </ul>
                </div>
            @endif

            {{-- Menu Keuangan Akutansi --}}
            @php
                $keuanganMenu = [
                    'keuangan.periode.read',
                    'keuangan.kategori-akun.read',
                    'keuangan.akun-keuangan.read',
                    'keuangan.transaksi-jurnal.create',
                    'keuangan.laporan-jurnal.read',
                    'keuangan.buku-besar.read',
                    'keuangan.neraca-saldo.read',
                ];
            @endphp
            @if (auth()->user()->canAny($keuanganMenu))
                <!-- Menu Group - Etalase -->
                <div>
                    <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                            Keuangan
                        </span>

                        <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                            class="mx-auto fill-current menu-group-icon" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                fill="" />
                        </svg>
                    </h3>

                    <ul class="flex flex-col gap-2 mb-6">
                        <!-- Menu Item Periode Keuangan  -->
                        @can('keuangan.periode.read')
                            <li>
                                <a href="{{ route('keuangan.periodeKeuangan.index') }}"
                                    @click="selected = (selected === 'PeriodeKeuangan' ? '':'PeriodeKeuangan')"
                                    class="menu-item group"
                                    :class="(selected === 'PeriodeKeuangan') && (page === 'PeriodeKeuangan') ?
                                    'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" width="24"
                                        height="24" viewBox="0 0 2048 2048">
                                        <title>date-time</title>
                                        <path fill="currentColor"
                                            d="M1792 993q60 41 107 93t81 114t50 131t18 141q0 119-45 224t-124 183t-183 123t-224 46q-91 0-176-27t-156-78t-126-122t-85-157H128V128h256V0h128v128h896V0h128v128h256zM256 256v256h1408V256h-128v128h-128V256H512v128H384V256zm643 1280q-3-31-3-64q0-86 24-167t73-153h-97v-128h128v86q41-51 91-90t108-67t121-42t128-15q100 0 192 33V640H256v896zm573 384q93 0 174-35t142-96t96-142t36-175q0-93-35-174t-96-142t-142-96t-175-36q-93 0-174 35t-142 96t-96 142t-36 175q0 93 35 174t96 142t142 96t175 36m64-512h192v128h-320v-384h128zM384 1024h128v128H384zm256 0h128v128H640zm0-256h128v128H640zm-256 512h128v128H384zm256 0h128v128H640zm384-384H896V768h128zm256 0h-128V768h128zm256 0h-128V768h128z" />
                                    </svg>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Periode Keuangan
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Periode Keuangan -->

                        <!-- Menu Item Kategori Akun -->
                        @can('keuangan.kategori-akun.read')
                            <li>
                                <a href="{{ route('keuangan.kategoriAkun.index') }}"
                                    @click="selected = (selected === 'KategoriAkun' ? '':'KategoriAkun')"
                                    class="menu-item group"
                                    :class="(selected === 'KategoriAkun') && (page === 'KategoriAkun') ? 'menu-item-active' :
                                    'menu-item-inactive'">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        :class="(selected === 'KategoriAkun') && (page === 'KategoriAkun') ?
                                        'menu-item-icon-active' :
                                        'menu-item-icon-inactive'"
                                        width="24" height="24" viewBox="0 0 24 24">
                                        <title>category</title>
                                        <path fill="none" stroke="currentColor" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="2"
                                            d="M4 4h6v6H4zm10 0h6v6h-6zM4 14h6v6H4zm10 3a3 3 0 1 0 6 0a3 3 0 1 0-6 0" />
                                    </svg>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Kategori Akun
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Kategori Akun -->

                        <!-- Menu Item Akun Akutansi -->
                        @can('keuangan.akun-keuangan.read')
                            <li>
                                <a href="{{ route('keuangan.akunKeuangan.index') }}"
                                    @click="selected = (selected === 'AkunKeuangan' ? '':'AkunKeuangan')"
                                    class="menu-item group"
                                    :class="(selected === 'AkunKeuangan') && (page === 'AkunKeuangan') ? 'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        :class="(selected === 'AkunKeuangan') && (page === 'AkunKeuangan') ?
                                        'menu-item-icon-active' :
                                        'menu-item-icon-inactive'"
                                        width="24" height="24" viewBox="0 0 24 24">
                                        <title>outline-account-tree</title>
                                        <path fill="currentColor"
                                            d="M22 11V3h-7v3H9V3H2v8h7V8h2v10h4v3h7v-8h-7v3h-2V8h2v3zM7 9H4V5h3zm10 6h3v4h-3zm0-10h3v4h-3z" />
                                    </svg>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Akun
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Akun Akutansi -->

                        <!-- Menu Item Jurnal -->
                        @can('keuangan.transaksi-jurnal.create')
                            <li>
                                <a href="{{ route('keuangan.transaksiJurnal.create') }}"
                                    @click="selected = (selected === 'Jurnal' ? '':'Jurnal')" class="menu-item group"
                                    :class="(selected === 'Jurnal') && (page === 'Jurnal') ?
                                    'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        :class="(selected === 'Jurnal') && (page === 'Jurnal') ?
                                        'menu-item-icon-active' :
                                        'menu-item-icon-inactive'"
                                        width="24" height="24" viewBox="0 0 24 24">
                                        <title>table</title>
                                        <g fill="none" stroke="currentColor" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="1.5">
                                            <rect width="18.5" height="16.5" x="2.75" y="3.75" rx="3" />
                                            <path d="M2.75 7.75h18.5M2.75 14h18.5M8.92 7.75v12.5m6.17-12.5v12.5" />
                                        </g>
                                    </svg>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Jurnal
                                    </span>
                                </a>
                            </li>
                        @endcan
                        <!-- Menu Item Jurnal -->

                        <!-- Menu Group Item DaftarLaporan  -->
                        @canany(['keuangan.laporan-jurnal.read', 'keuangan.buku-besar.read',
                            'keuangan.neraca-saldo.read'])
                            <li>
                                <a href="#"
                                    @click.prevent="selected = (selected === 'DaftarLaporan' ? '':'DaftarLaporan')"
                                    class="menu-item group"
                                    :class="(selected === 'DaftarLaporan') || (page === 'LapoaranJurnal' ||
                                        page === 'BukuBesar' ||
                                        page === 'NeracaSaldo' || page === 'proFormLayout') ? 'menu-item-active' :
                                    'menu-item-inactive'">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        :class="(selected === 'DaftarLaporan') ||
                                        (page === 'LapoaranJurnal' ||
                                            page === 'BukuBesar' ||
                                            page === 'NeracaSaldo' ||
                                            page === 'proFormLayout') ?
                                        'menu-item-icon-active' :
                                        'menu-item-icon-inactive'"
                                        width="24" height="24" viewBox="0 0 16 16">
                                        <title>report-outline</title>
                                        <path fill="none" stroke="currentColor" stroke-linejoin="round"
                                            d="M7.563 1.545H2.5v10.91h9V5.364M7.563 1.545L11.5 5.364M7.563 1.545v3.819H11.5m-7 9.136h9v-7M4 7.5h6M4 5h2m-2 5h6" />
                                    </svg>

                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Daftar Laporan
                                    </span>

                                    <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                        :class="[(selected === 'DaftarLaporan') ? 'menu-item-arrow-active' :
                                            'menu-item-arrow-inactive',
                                            sidebarToggle ? 'lg:hidden' : ''
                                        ]"
                                        width="20" height="20" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>

                                <!-- Dropdown Menu Start -->
                                <div class="overflow-hidden transform translate"
                                    :class="(selected === 'DaftarLaporan') ? 'block' : 'hidden'">
                                    <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                        class="flex flex-col mt-2 menu-dropdown pl-9">
                                        <!-- Lapoaran Jurnal -->
                                        @can('keuangan.laporan-jurnal.read')
                                            <li>
                                                <a href="{{ route('keuangan.laporanJurnal.index') }}"
                                                    class="menu-dropdown-item group"
                                                    :class="page === 'LapoaranJurnal' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24">
                                                        <title>file-table</title>
                                                        <path fill="currentColor"
                                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm-4 17H7v-2h3zm0-3H7v-2h3zm0-3H7v-2h3zm4 6h-3v-2h3zm0-3h-3v-2h3zm0-3h-3v-2h3zm-1-4V3.5L18.5 9z" />
                                                    </svg>
                                                    Laporan Jurnal
                                                </a>
                                            </li>
                                        @endcan

                                        @can('keuangan.buku-besar.read')
                                            <!-- Buku Besar -->
                                            <li>
                                                <a href="{{ route('keuangan.bukuBesar.index') }}"
                                                    class="menu-dropdown-item group"
                                                    :class="page === 'BukuBesar' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-5" width="22"
                                                        height="24" viewBox="0 0 1696 1536">
                                                        <title>book</title>
                                                        <path fill="currentColor"
                                                            d="M1671 350q40 57 18 129l-275 906q-19 64-76.5 107.5T1215 1536H292q-77 0-148.5-53.5T44 1351q-24-67-2-127q0-4 3-27t4-37q1-8-3-21.5t-3-19.5q2-11 8-21t16.5-23.5T84 1051q23-38 45-91.5t30-91.5q3-10 .5-30t-.5-28q3-11 17-28t17-23q21-36 42-92t25-90q1-9-2.5-32t.5-28q4-13 22-30.5t22-22.5q19-26 42.5-84.5T372 283q1-8-3-25.5t-2-26.5q2-8 9-18t18-23t17-21q8-12 16.5-30.5t15-35t16-36t19.5-32T504.5 12t36-11.5T588 6l-1 3q38-9 51-9h761q74 0 114 56t18 130l-274 906q-36 119-71.5 153.5T1057 1280H188q-27 0-38 15q-11 16-1 43q24 70 144 70h923q29 0 56-15.5t35-41.5l300-987q7-22 5-57q38 15 59 43m-1064 2q-4 13 2 22.5t20 9.5h608q13 0 25.5-9.5T1279 352l21-64q4-13-2-22.5t-20-9.5H670q-13 0-25.5 9.5T628 288zm-83 256q-4 13 2 22.5t20 9.5h608q13 0 25.5-9.5T1196 608l21-64q4-13-2-22.5t-20-9.5H587q-13 0-25.5 9.5T545 544z" />
                                                    </svg>
                                                    Buku Besar
                                                </a>
                                            </li>
                                        @endcan

                                        @can('keuangan.neraca-saldo.read')
                                            <!-- Neraca Saldo -->
                                            <li>
                                                <a href="{{ route('keuangan.neracaSaldo.index') }}"
                                                    class="menu-dropdown-item group"
                                                    :class="page === 'NeracaSaldo' ? 'menu-dropdown-item-active' :
                                                        'menu-dropdown-item-inactive'">
                                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24">
                                                        <title>file-table</title>
                                                        <path fill="currentColor"
                                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm-4 17H7v-2h3zm0-3H7v-2h3zm0-3H7v-2h3zm4 6h-3v-2h3zm0-3h-3v-2h3zm0-3h-3v-2h3zm-1-4V3.5L18.5 9z" />
                                                    </svg>
                                                    Neraca Saldo
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                                <!-- Dropdown Menu End -->
                            </li>
                        @endcanany
                        <!-- Menu Item DaftarLaporan -->

                        <!-- Persetujuan Upah Manager -->
                        @if(auth()->user()->hasRole(['Manager Dukungan & Layanan', 'Superadmin']))
                        <li>
                            <a href="#" @click.prevent="selected = (selected === 'persetujuanUpahManager' ? '':'persetujuanUpahManager')"
                                class="menu-item group"
                                :class="(selected === 'persetujuanUpahManager') || (page === 'managerPersetujuanUpahProperti' || page === 'managerPersetujuanUpahKontraktor' || page === 'managerPersetujuanUpahKawasan') ? 'menu-item-active' : 'menu-item-inactive'">
                                <svg :class="(selected === 'persetujuanUpahManager') || (page === 'managerPersetujuanUpahProperti' || page === 'managerPersetujuanUpahKontraktor' || page === 'managerPersetujuanUpahKawasan') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                    class="w-6 h-6 size-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M10.5 8a3 3 0 1 0 0 6a3 3 0 0 0 0-6M9 11a1.5 1.5 0 1 1 3 0a1.5 1.5 0 0 1-3 0M2 7.25A2.25 2.25 0 0 1 4.25 5h12.5A2.25 2.25 0 0 1 19 7.25v3.924A6.5 6.5 0 0 0 17.5 11V9.5h-.75a2.25 2.25 0 0 1-2.25-2.25V6.5h-8v.75A2.25 2.25 0 0 1 4.25 9.5H3.5v3h.75a2.25 2.25 0 0 1 2.25 2.25v.75h4.813c-.154.478-.255.98-.294 1.5H4.25A2.25 2.25 0 0 1 2 14.75zM4.401 18.5h6.676c.08.523.223 1.026.421 1.5H7a3 3 0 0 1-2.599-1.5M20.5 11.732A6.5 6.5 0 0 1 22 12.81V10a3 3 0 0 0-1.5-2.599zM4.25 6.5a.75.75 0 0 0-.75.75V8h.75A.75.75 0 0 0 5 7.25V6.5zM17.5 8v-.75a.75.75 0 0 0-.75-.75H16v.75c0 .414.336.75.75.75zm-14 6.75c0 .414.336.75.75.75H5v-.75a.75.75 0 0 0-.75-.75H3.5zm10.778-.774a2 2 0 0 1-1.441 2.496l-.584.144a5.7 5.7 0 0 0 .006 1.808l.54.13a2 2 0 0 1 1.45 2.51l-.187.631c.44.386.94.699 1.484.922l.494-.519a2 2 0 0 1 2.899 0l.498.525a5.3 5.3 0 0 0 1.483-.913l-.198-.686a2 2 0 0 1 1.441-2.496l.584-.144a5.7 5 0 0 0-.006-1.808l-.54-.13a2 2 0 0 1-1.45-2.51l.187-.63a5.3 5.3 0 0 0-1.484-.922l-.493.518a2 2 0 0 1-2.9 0l-.498-.525a5.3 5.3 0 0 0-1.483.912zM17.5 19c-.8 0-1.45-.672-1.45-1.5S16.7 16 17.5 16s1.45.672 1.45 1.5S18.3 19 17.5 19" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pers. Upah Manager</span>
                                <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                    :class="[(selected === 'persetujuanUpahManager') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '']"
                                    width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                            <div class="overflow-hidden transform translate" :class="(selected === 'persetujuanUpahManager') ? 'block' : 'hidden'">
                                <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                    <li>
                                        <a href="{{ route('manager.persetujuanUpahProperti.index') }}" class="menu-dropdown-item group"
                                            :class="page === 'managerPersetujuanUpahProperti' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                            Upah Pemb. Unit
                                        </a>
                                    </li>
                                    {{-- 
                                    <li>
                                        <a href="{{ route('manager.persetujuanUpahKawasan.index') }}" class="menu-dropdown-item group"
                                            :class="page === 'managerPersetujuanUpahKawasan' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                            Upah Pemb. Kawasan
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('manager.persetujuanUpahKontraktor.index') }}" class="menu-dropdown-item group"
                                            :class="page === 'managerPersetujuanUpahKontraktor' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                            Upah Pemb. Proyek
                                        </a>
                                    </li>
                                    --}}
                                </ul>
                            </div>
                        </li>
                        @endif

                        <!-- Persetujuan Upah Akuntan -->
                        @if(auth()->user()->hasRole(['Staff Akuntansi', 'Superadmin']))
                        <li>
                            <a href="#" @click.prevent="selected = (selected === 'persetujuanUpahAkuntan' ? '':'persetujuanUpahAkuntan')"
                                class="menu-item group"
                                :class="(selected === 'persetujuanUpahAkuntan') || (page === 'akuntanPersetujuanUpahProperti' || page === 'akuntanPersetujuanUpahKontraktor' || page === 'akuntanPersetujuanUpahKawasan') ? 'menu-item-active' : 'menu-item-inactive'">
                                <svg :class="(selected === 'persetujuanUpahAkuntan') || (page === 'akuntanPersetujuanUpahProperti' || page === 'akuntanPersetujuanUpahKontraktor' || page === 'akuntanPersetujuanUpahKawasan') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                    class="w-6 h-6 size-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M10.5 8a3 3 0 1 0 0 6a3 3 0 0 0 0-6M9 11a1.5 1.5 0 1 1 3 0a1.5 1.5 0 0 1-3 0M2 7.25A2.25 2.25 0 0 1 4.25 5h12.5A2.25 2.25 0 0 1 19 7.25v3.924A6.5 6.5 0 0 0 17.5 11V9.5h-.75a2.25 2.25 0 0 1-2.25-2.25V6.5h-8v.75A2.25 2.25 0 0 1 4.25 9.5H3.5v3h.75a2.25 2.25 0 0 1 2.25 2.25v.75h4.813c-.154.478-.255.98-.294 1.5H4.25A2.25 2.25 0 0 1 2 14.75zM4.401 18.5h6.676c.08.523.223 1.026.421 1.5H7a3 3 0 0 1-2.599-1.5M20.5 11.732A6.5 6.5 0 0 1 22 12.81V10a3 3 0 0 0-1.5-2.599zM4.25 6.5a.75.75 0 0 0-.75.75V8h.75A.75.75 0 0 0 5 7.25V6.5zM17.5 8v-.75a.75.75 0 0 0-.75-.75H16v.75c0 .414.336.75.75.75zm-14 6.75c0 .414.336.75.75.75H5v-.75a.75.75 0 0 0-.75-.75H3.5zm10.778-.774a2 2 0 0 1-1.441 2.496l-.584.144a5.7 5.7 0 0 0 .006 1.808l.54.13a2 2 0 0 1 1.45 2.51l-.187.631c.44.386.94.699 1.484.922l.494-.519a2 2 0 0 1 2.899 0l.498.525a5.3 5.3 0 0 0 1.483-.913l-.198-.686a2 2 0 0 1 1.441-2.496l.584-.144a5.7 5 0 0 0-.006-1.808l-.54-.13a2 2 0 0 1-1.45-2.51l.187-.63a5.3 5.3 0 0 0-1.484-.922l-.493.518a2 2 0 0 1-2.9 0l-.498-.525a5.3 5.3 0 0 0-1.483.912zM17.5 19c-.8 0-1.45-.672-1.45-1.5S16.7 16 17.5 16s1.45.672 1.45 1.5S18.3 19 17.5 19" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Pers. Upah Akuntan</span>
                                <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                    :class="[(selected === 'persetujuanUpahAkuntan') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '']"
                                    width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                            <div class="overflow-hidden transform translate" :class="(selected === 'persetujuanUpahAkuntan') ? 'block' : 'hidden'">
                                <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                    <li>
                                        <a href="{{ route('akuntan.persetujuanUpahProperti.index') }}" class="menu-dropdown-item group"
                                            :class="page === 'akuntanPersetujuanUpahProperti' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                            Upah Pemb. Unit
                                        </a>
                                    </li>
                                    {{-- 
                                    <li>
                                        <a href="{{ route('akuntan.persetujuanUpahKawasan.index') }}" class="menu-dropdown-item group"
                                            :class="page === 'akuntanPersetujuanUpahKawasan' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                            Upah Pemb. Kawasan
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('akuntan.persetujuanUpahKontraktor.index') }}" class="menu-dropdown-item group"
                                            :class="page === 'akuntanPersetujuanUpahKontraktor' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                            Upah Pemb. Proyek
                                        </a>
                                    </li>
                                    --}}
                                </ul>
                            </div>
                        </li>
                        @endif


                        <!-- Upah Harian Tukang -->
                        @if(auth()->user()->hasRole(['Staff Keuangan', 'Superadmin']))
                        <li>
                            <a href="#" @click.prevent="selected = (selected === 'upahHarianTukangKeuangan' ? '':'upahHarianTukangKeuangan')"
                                class="menu-item group"
                                :class="(selected === 'upahHarianTukangKeuangan') || (page === 'DaftarPengajuanUpahKeuangan') ? 'menu-item-active' : 'menu-item-inactive'">
                                <svg :class="(selected === 'upahHarianTukangKeuangan') || (page === 'DaftarPengajuanUpahKeuangan') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                    class="w-6 h-6 size-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <title>worker-daily-wage</title>
                                    <path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Upah Harian Tukang</span>
                                <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                    :class="[(selected === 'upahHarianTukangKeuangan') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '']"
                                    width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                            <div class="overflow-hidden transform translate" :class="(selected === 'upahHarianTukangKeuangan') ? 'block' : 'hidden'">
                                <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                    <li>
                                        <a href="#" class="menu-dropdown-item group"
                                            :class="page === 'DaftarPengajuanUpahKeuangan' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                            Daftar Pengajuan
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endif

                    </ul>
                </div>
                <!-- Menu Group - Keuangan -->
            @endif

            <!-- Gudang -  Group -->
            <div>
                <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Gudang
                    </span>

                    <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                        class="mx-auto fill-current menu-group-icon" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                            fill="" />
                    </svg>
                </h3>

                <ul class="flex flex-col gap-2 mb-6">
                    <!-- menu stock barang dan transfer stok -->
                    <li>
                        <a href="{{ route('gudang.stockBarang.index') }}"
                            @click="selected = (selected === 'StokBarangGudang' ? '' : 'StokBarangGudang')"
                            class="menu-item group"
                            :class="(selected === 'StokBarangGudang') && (page === 'StokBarangGudang') ?
                            'menu-item-active' :
                            'menu-item-inactive'">

                            <svg :class="(selected === 'Packages') && (page === 'Packages') ? 'menu-item-icon-active' : ''"
                                width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                fill="none" stroke="currentColor" stroke-width="1.5" class="size-6">

                                <title>packages</title>

                                <g fill="none" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2">
                                    <path d="m7 16.5l-5-3l5-3l5 3V19l-5 3z" />
                                    <path
                                        d="M2 13.5V19l5 3m0-5.455l5-3.03m5 2.985l-5-3l5-3l5 3V19l-5 3zM12 19l5 3m0-5.5l5-3m-10 0V8L7 5l5-3l5 3v5.5M7 5.03v5.455M12 8l5-3" />
                                </g>
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Stok Barang
                            </span>
                        </a>
                    </li>
                    <!-- menu stock barang dan transfer stok -->

                    <!-- Menu Barang Rusak -->
                    <li>
                        <a href="{{ route('gudang.barangRusak.index') }}"
                            @click="selected = (selected === 'DaftarBarangRusak' ? '' : 'DaftarBarangRusak')"
                            class="menu-item group"
                            :class="(selected === 'DaftarBarangRusak') && (page === 'DaftarBarangRusak') ?
                            'menu-item-active' :
                            'menu-item-inactive'">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                :class="(selected === 'DaftarBarangRusak') && (page === 'DaftarBarangRusak') ? 'menu-item-icon-active' : ''"
                                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25m-2.25-2.25 2.25-2.25m-2.25 2.25-2.25 2.25M3.75 7.5l.625-10.632A2.25 2.25 0 0 1 6.622 3h10.756a2.25 2.25 0 0 1 2.247 2.118L20.25 7.5m-16.5 0h16.5" />
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Barang Rusak
                            </span>
                        </a>
                    </li>
                    <!-- Menu Barang Rusak -->

                    <!-- Menu Group Item Barang Rakitan -->
                    <li>
                        <a href="#"
                            @click.prevent="selected = (selected === 'BarangRakitanGroup' ? '':'BarangRakitanGroup')"
                            class="menu-item group"
                            :class="(selected === 'BarangRakitanGroup') || (page === 'KomposisiRakitan' || page === 'ProduksiRakitan') ? 'menu-item-active' :
                            'menu-item-inactive'">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                :class="(selected === 'BarangRakitanGroup') ||
                                (page === 'KomposisiRakitan' ||
                                    page === 'ProduksiRakitan') ?
                                'menu-item-icon-active' :
                                'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" class="size-6">
                                <title>assembly-line</title>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 7.5 12 3l8 4.5-8 4.5L4 7.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 12l8 4.5 8-4.5M4 16.5l8 4.5 8-4.5M8.5 5.1l8 4.5M15.5 5.1l-8 4.5" />
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Barang Rakitan
                            </span>

                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'BarangRakitanGroup') ? 'menu-item-arrow-active' :
                                    'menu-item-arrow-inactive',
                                    sidebarToggle ? 'lg:hidden' : ''
                                ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>

                        <!-- Dropdown Menu Start -->
                        <div class="overflow-hidden transform translate"
                            :class="(selected === 'BarangRakitanGroup') ? 'block' : 'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col mt-2 menu-dropdown pl-9">

                                <!-- Komposisi Rakitan -->
                                <li>
                                    <a href="{{ route('gudang.komposisiRakitan.index') }}"
                                        class="menu-dropdown-item group flex items-center gap-3"
                                        :class="page === 'KomposisiRakitan' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            :class="(selected === 'BarangRakitanGroup') ||
                                            (page === 'KomposisiRakitan') ?
                                            'menu-item-icon-active' :
                                            'menu-item-icon-inactive'"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <title>recipe-composition</title>
                                            <g stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="1.5">
                                                <path d="M8 4h8M8 8h8M8 12h5" />
                                                <path d="M5 4h.01M5 8h.01M5 12h.01" />
                                                <path d="M4 20h16" />
                                                <path d="M7 17h10l-2-4H9l-2 4Z" />
                                            </g>
                                        </svg>
                                        Komposisi Rakitan
                                    </a>
                                </li>

                                <!-- Produksi Rakitan -->
                                <li>
                                    <a href="{{ route('gudang.produksiRakitan.index') }}"
                                        class="menu-dropdown-item group flex items-center"
                                        :class="page === 'ProduksiRakitan' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <title>production-assembly</title>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 17h4l2-4h6l2 4h4" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 17v3h12v-3M8 13V8l4-3 4 3v5M10 10h4" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4 7h3m10 0h3M4 11h2m12 0h2" />
                                        </svg>
                                        Produksi Rakitan
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Barang Rakitan -->

                    <!-- Menu Group Item Master Gudang  -->
                    <li>
                        <a href="#"
                            @click.prevent="selected = (selected === 'MasterGudang' ? '':'MasterGudang')"
                            class="menu-item group"
                            :class="(selected === 'MasterGudang') || (page === 'MasterSatuan' || page === 'MasterBarang' ||
                                page === 'proFormLayout') ? 'menu-item-active' :
                            'menu-item-inactive'">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                :class="(selected === 'MasterGudang') ||
                                (page === 'MasterSatuan' ||
                                    page === 'MasterBarang' ||
                                    page === 'proFormLayout') ?
                                'menu-item-icon-active' :
                                'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 16 16">
                                <title>report-outline</title>
                                <path fill="none" stroke="currentColor" stroke-linejoin="round"
                                    d="M7.563 1.545H2.5v10.91h9V5.364M7.563 1.545L11.5 5.364M7.563 1.545v3.819H11.5m-7 9.136h9v-7M4 7.5h6M4 5h2m-2 5h6" />
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Master Gudang
                            </span>

                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'MasterGudang') ? 'menu-item-arrow-active' :
                                    'menu-item-arrow-inactive',
                                    sidebarToggle ? 'lg:hidden' : ''
                                ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>

                        <!-- Dropdown Menu Start -->
                        <div class="overflow-hidden transform translate"
                            :class="(selected === 'MasterGudang') ? 'block' : 'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col mt-2 menu-dropdown pl-9">

                                <!-- Master Satuan -->
                                <li>
                                    <a href="{{ route('gudang.masterSatuanBarang.index') }}"
                                        class="menu-dropdown-item group flex items-center gap-3"
                                        :class="page === 'MasterSatuan' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            :class="(selected === 'MasterGudang') ||
                                            (page === 'MasterSatuan' ||
                                                page === 'proFormLayout') ?
                                            'menu-item-icon-active' :
                                            'menu-item-icon-inactive'"
                                            width="24" height="24" viewBox="0 0 16 16">
                                            <title>report-outline</title>
                                            <g fill="none" stroke="currentColor" stroke-linejoin="round">
                                                <path
                                                    d="M7.563 1.545H2.5v10.91h9V5.364M7.563 1.545L11.5 5.364M7.563 1.545v3.819H11.5m-7 9.136h9v-7M4 7.5h6M4 5h2m-2 5h6" />
                                            </g>
                                        </svg>
                                        Master Satuan
                                    </a>
                                </li>

                                <!-- Daftar Material Barang -->
                                <li>
                                    <a href="{{ route('gudang.masterBarang.index') }}"
                                        class="menu-dropdown-item group flex items-center"
                                        :class="page === 'MasterBarang' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <title>package</title>
                                            <path fill="none" stroke="currentColor" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="1.5" d="M12 22c-.818 0-1.6-.33-3.163-.99C4.946 19.366 3 18.543 3 17.16V7
                                                m9 15c.818 0 1.6-.33 3.163-.99C19.054 19.366 21 18.543 21 17.16V7
                                                m-9 15V11.355
                                                M8.326 9.691L5.405 8.278C3.802 7.502 3 7.114 3 6.5s.802-1.002 2.405-1.778
                                                l2.92-1.413C10.13 2.436 11.03 2 12 2s1.871.436 3.674 1.309l2.921 1.413
                                                C20.198 5.498 21 5.886 21 6.5s-.802 1.002-2.405 1.778l-2.92 1.413
                                                C13.87 10.564 12.97 11 12 11s-1.871-.436-3.674-1.309
                                                M6 12l2 1
                                                m9-9L7 9" />
                                        </svg>
                                        Material/Barang
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Master Master Gudang -->

                    <!-- Menu Group Item Nota Barang Masuk  -->
                    <li>
                        <a href="#"
                            @click.prevent="selected = (selected === 'NotaMasukGroup' ? '':'NotaMasukGroup')"
                            class="menu-item group"
                            :class="(selected === 'NotaMasukGroup') || (page === 'TambahNotaMasuk' ||
                                page === 'DaftarNotaMasuk' || page === 'proFormLayout') ? 'menu-item-active' :
                            'menu-item-inactive'">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                :class="(selected === 'NotaMasukGroup') ||
                                (page === 'TambahNotaMasuk' ||
                                    page === 'DaftarNotaMasuk' ||
                                    page === 'proFormLayout') ?
                                'menu-item-icon-active' :
                                'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 24 24">
                                <title>package-receive</title>
                                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5">
                                    <path stroke-linejoin="round" d="M12 22c-.818 0-1.6-.325-3.163-.974C4.946 19.41 3 18.602 3 17.243V7.745
                                        M12 22c.818 0 1.6-.325 3.163-.974C19.054 19.41 21 18.602 21 17.243V7.745
                                        M12 22v-9.831
                                        M3 7.745c0 .603.802.985 2.405 1.747l2.92 1.39C10.13 11.74 11.03 12.17 12 12.17
                                        M3 7.745c0-.604.802-.986 2.405-1.748L7.5 5
                                        M21 7.745c0 .603-.802.985-2.405 1.747l-2.92 1.39C13.87 11.74 12.97 12.17 12 12.17
                                        m9-4.424c0-.604-.802-.986-2.405-1.748L16.5 5
                                        M6 13.152l2 .983" />
                                    <path d="M12.004 2v7
                                        m0 0c.263.004.522-.18.714-.405L14 7.062
                                        M12.004 9c-.254-.003-.511-.186-.714-.405L10 7.062" />
                                </g>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Nota Masuk
                            </span>
                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'NotaMasukGroup') ? 'menu-item-arrow-active' :
                                    'menu-item-arrow-inactive',
                                    sidebarToggle ? 'lg:hidden' : ''
                                ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>

                        <!-- Dropdown Menu Start -->
                        <div class="overflow-hidden transform translate"
                            :class="(selected === 'NotaMasukGroup') ? 'block' : 'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col mt-2 menu-dropdown pl-9">

                                <li>
                                    <a href="{{ route('gudang.notaBarangMasuk.create') }}"
                                        class="menu-dropdown-item group flex items-center"
                                        :class="page === 'TambahNotaMasuk' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            :class="(selected === 'NotaMasukGroup') ||
                                            (page === 'TambahNotaMasuk' ||
                                                page === 'proFormLayout') ?
                                            'menu-item-icon-active' :
                                            'menu-item-icon-inactive'"
                                            width="24" height="24" viewBox="0 0 24 24">
                                            <title>package-add</title>
                                            <g fill="none" stroke="currentColor" stroke-linecap="round"
                                                stroke-width="1.5">
                                                <path stroke-linejoin="round" d="M11 22c-.818 0-1.6-.33-3.163-.99C3.946 19.366 2 18.543 2 17.16V7
                                                    m9 15V11.355
                                                    M11 22c.725 0 1.293-.26 2.5-.777
                                                    M20 7v4" />
                                                <path d="M15 17.5h7
                                                    M18.5 21v-7" />
                                                <path stroke-linejoin="round" d="M7.326 9.691L4.405 8.278C2.802 7.502 2 7.114 2 6.5
                                                    s.802-1.002 2.405-1.778l2.92-1.413C9.13 2.436 10.03 2 11 2
                                                    s1.871.436 3.674 1.309l2.921 1.413C19.198 5.498 20 5.886 20 6.5
                                                    s-.802 1.002-2.405 1.778l-2.92 1.413C12.87 10.564 11.97 11 11 11
                                                    s-1.871-.436-3.674-1.309
                                                    M5 12l2 1
                                                    m9-9L6 9" />
                                            </g>
                                        </svg>
                                        Tambah Nota Masuk
                                    </a>
                                </li>

                                <!-- Daftar Nota Masuk -->
                                <li>
                                    <a href="{{ route('gudang.daftarNotaMasuk.index') }}"
                                        class="menu-dropdown-item group flex items-center"
                                        :class="page === 'DaftarNotaMasuk' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                            <title>receipt</title>
                                            <g fill="none" stroke="currentColor" stroke-linecap="round"
                                                stroke-width="2">
                                                <path stroke-linejoin="round"
                                                    d="M19 21H7a4 4 0 0 1-4-4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v13c0 1.657.343 3 2 3" />
                                                <path stroke-linejoin="round"
                                                    d="M21 10a2 2 0 0 0-2-2h-2v10.5c0 1.38.62 2.5 2 2.5s2-1.12 2-2.5z" />
                                                <path d="M13 11H7m6-4H7m3 8H7" />
                                            </g>
                                        </svg>
                                        Daftar Nota Masuk
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Nota Barang Masuk-->

                    <!-- Menu Group Item Material Proyek  -->
                    <li>
                        <a href="#"
                            @click.prevent="selected = (selected === 'MaterialProyekGroup' ? '':'MaterialProyekGroup')"
                            class="menu-item group"
                            :class="(selected === 'MaterialProyekGroup') || (page === 'PermintaanBarang' ||
                                page === 'BarangReturnGudang' || page === 'proFormLayout') ? 'menu-item-active' :
                            'menu-item-inactive'">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                :class="(selected === 'MaterialProyekGroup') ||
                                (page === 'PermintaanBarang' ||
                                    page === 'BarangReturnGudang' ||
                                    page === 'proFormLayout') ?
                                'menu-item-icon-active' :
                                'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 24 24">
                                <title>home-up</title>
                                <g fill="none" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="1.5">
                                    <path d="M9 21v-6a2 2 0 0 1 2-2h2c.641 0 1.212.302 1.578.771" />
                                    <path d="M20.136 11.136L12 3l-9 9h2v7a2 2 0 0 0 2 2h6.344" />
                                    <path d="M19 22v-6m3 3l-3-3l-3 3" />
                                </g>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Material Proyek
                            </span>
                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'MaterialProyekGroup') ? 'menu-item-arrow-active' :
                                    'menu-item-arrow-inactive',
                                    sidebarToggle ? 'lg:hidden' : ''
                                ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>

                        <!-- Dropdown Menu Start -->
                        <div class="overflow-hidden transform translate"
                            :class="(selected === 'MaterialProyekGroup') ? 'block' : 'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col mt-2 menu-dropdown pl-9">

                                <!-- Sub-Menu Permintaan -->
                                <li class="text-[10px] font-semibold text-gray-400 uppercase mt-4 mb-1">
                                    Permintaan (Order)
                                </li>
                                <li>
                                    <a href="{{ route('gudang.permintaanBarang.index', ['jenis_order' => 'pembangunan_unit']) }}"
                                        class="menu-dropdown-item group flex items-center gap-2"
                                        :class="page === 'PermintaanBarangUnit' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></div>
                                        Pemb. Unit
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('gudang.permintaanBarang.index', ['jenis_order' => 'pembangunan_kawasan']) }}"
                                        class="menu-dropdown-item group flex items-center gap-2"
                                        :class="page === 'PermintaanBarangKawasan' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></div>
                                        Pemb. Kawasan
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('gudang.permintaanBarang.index', ['jenis_order' => 'pembangunan_proyek_mangoon']) }}"
                                        class="menu-dropdown-item group flex items-center gap-2"
                                        :class="page === 'PermintaanBarangProyek' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></div>
                                        Proyek Mangoon
                                    </a>
                                </li>

                                <!-- Sub-Menu Retur -->
                                <li class="text-[10px] font-semibold text-gray-400 uppercase mt-4 mb-1">
                                    Retur Barang
                                </li>
                                <li>
                                    <a href="{{ route('gudang.returnBarang.unit.index') }}"
                                        class="menu-dropdown-item group flex items-center gap-2"
                                        :class="page === 'BarangReturnUnit' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></div>
                                        Retur Unit
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('gudang.returnBarang.kawasan.index') }}"
                                        class="menu-dropdown-item group flex items-center gap-2"
                                        :class="page === 'BarangReturnKawasan' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></div>
                                        Retur Kawasan
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('gudang.returnBarang.proyek.index') }}"
                                        class="menu-dropdown-item group flex items-center gap-2"
                                        :class="page === 'BarangReturnProyek' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-current opacity-40"></div>
                                        Retur Proyek Mangoon
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Material Proyek-->




                    <!-- Menu Group Item Upah Harian Tukang -->
                    <li>
                        <a href="#"
                            @click.prevent="selected = (selected === 'UpahHarianTukangGroup' ? '':'UpahHarianTukangGroup')"
                            class="menu-item group"
                            :class="(selected === 'UpahHarianTukangGroup') || (page === 'MasterTukangHarian' || page === 'DaftarPengajuanUpah') ? 'menu-item-active' :
                            'menu-item-inactive'">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                :class="(selected === 'UpahHarianTukangGroup') ||
                                (page === 'MasterTukangHarian' ||
                                    page === 'DaftarPengajuanUpah') ?
                                'menu-item-icon-active' :
                                'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" class="size-6">
                                <title>worker-daily-wage</title>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.501 20.118a7.5 7.5 0 0 1 14.998 0M12 14.25v3m0 0h1.5m-1.5 0h-1.5" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75h6M9 15.75h3" />
                            </svg>

                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Upah Harian Tukang
                            </span>

                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'UpahHarianTukangGroup') ? 'menu-item-arrow-active' :
                                    'menu-item-arrow-inactive',
                                    sidebarToggle ? 'lg:hidden' : ''
                                ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke=""
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>

                        <!-- Dropdown Menu Start -->
                        <div class="overflow-hidden transform translate"
                            :class="(selected === 'UpahHarianTukangGroup') ? 'block' : 'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col mt-2 menu-dropdown pl-9">

                                <!-- Master Tukang -->
                                <li>
                                    <a href="#"
                                        class="menu-dropdown-item group flex items-center gap-3"
                                        :class="page === 'MasterTukangHarian' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            :class="(selected === 'UpahHarianTukangGroup') ||
                                            (page === 'MasterTukangHarian') ?
                                            'menu-item-icon-active' :
                                            'menu-item-icon-inactive'"
                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.5">
                                            <title>master-tukang</title>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                        </svg>
                                        Master Tukang
                                    </a>
                                </li>

                                <!-- Daftar Pengajuan Upah -->
                                <li>
                                    <a href="#"
                                        class="menu-dropdown-item group flex items-center gap-3"
                                        :class="page === 'DaftarPengajuanUpah' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            :class="(selected === 'UpahHarianTukangGroup') ||
                                            (page === 'DaftarPengajuanUpah') ?
                                            'menu-item-icon-active' :
                                            'menu-item-icon-inactive'"
                                            class="w-6 h-6 shrink-0"
                                            width="24" height="24" viewBox="1 1 22 22" fill="currentColor">
                                            <title>daftar-pengajuan-upah</title>
                                            <path d="M5.5 7a2.5 2.5 0 1 1 5 0a2.5 2.5 0 0 1-5 0M8 3a4 4 0 1 0 0 8a4 4 0 0 0 0-8m7.5 5a1.5 1.5 0 1 1 3 0a1.5 1.5 0 0 1-3 0M17 5a3 3 0 1 0 0 6a3 3 0 0 0 0-6m-5.25 8c.301 0 .588.06.851.166a2.5 2.5 0 0 0-1.393 1.334H4.25a.75.75 0 0 0-.75.75v.257l.007.08c.007.074.023.188.055.329c.066.281.198.656.459 1.029C4.514 17.65 5.578 18.5 8 18.5c1.407 0 2.355-.287 3-.665v1.661c-.791.312-1.777.504-3 .504c-2.828 0-4.39-1.025-5.208-2.195a4.5 4.5 0 0 1-.778-2.07A3 3 0 0 1 2 15.529v-.278A2.25 2.25 0 0 1 4.25 13zm.25 2.5a1.5 1.5 0 0 1 1.5-1.5h8a1.5 1.5 0 0 1 1.5 1.5v4a1.5 1.5 0 0 1-1.5 1.5h-8a1.5 1.5 0 0 1-1.5-1.5zm1 .5v1a2 2 0 0 0 2-2h-1a1 1 0 0 1-1 1m9 1v-1a1 1 0 0 1-1-1h-1a2 2 0 0 0 2 2m-2 3h1a1 1 0 0 1 1-1v-1a2 2 0 0 0-2 2m-7-2v1a1 1 0 0 1 1 1h1a2 2 0 0 0-2-2m4.5 1.25a1.75 1.75 0 1 0 0-3.5a1.75 1.75 0 0 0 0 3.5" />
                                        </svg>
                                        Daftar Pengajuan Upah
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Upah Harian Tukang -->


                </ul>
            </div>
            <!-- Gudang -  Group -->


            @canany(['produksi.master-qc-rap', 'produksi.permintaan-dibangun', 'produksi.pembangunan-unit', 'produksi.project-baru', 'produksi.pembangunan-proyek', 'produksi.buat-pembangunan-kawasan', 'produksi.pembangunan-kawasan', 'produksi.penamaan-upah', 'produksi.upah-properti', 'produksi.upah-kontraktor', 'produksi.upah-kawasan'])
            <!-- Master Produksi RAP -->
            <div>
                <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Produksi
                    </span>

                    <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                        class="mx-auto fill-current menu-group-icon" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                            fill="" />
                    </svg>
                </h3>

                <ul class="flex flex-col gap-2 mb-6">
                    <!-- Pembangunan Properti -->
                    @canany(['produksi.master-qc-rap', 'produksi.permintaan-dibangun', 'produksi.pembangunan-unit'])
                    <li>
                        <a href="#" @click.prevent="selected = (selected === 'Properti' ? '':'Properti')"
                            class="menu-item group"
                            :class="(selected === 'Properti') || (page === 'MasterQC-RAP' || page === 'PengajuanPembangunan' || page === 'pembangunanUnit') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg :class="(selected === 'Properti') || (page === 'MasterQC-RAP' || page === 'PengajuanPembangunan' || page === 'pembangunanUnit') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                class="w-6 h-6 size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Properti</span>
                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'Properti') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '']"
                                width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                        <div class="overflow-hidden transform translate" :class="(selected === 'Properti') ? 'block' : 'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                @can('produksi.master-qc-rap')
                                <li>
                                    <a href="{{ route('produksi.masterQcRap.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'MasterQC-RAP' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Master QC & RAP
                                    </a>
                                </li>
                                @endcan
                                @can('produksi.permintaan-dibangun')
                                <li>
                                    <a href="{{ route('produksi.pengajuanPembangunanUnit.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'PengajuanPembangunan' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Permintaan Dibangun
                                    </a>
                                </li>
                                @endcan
                                @can('produksi.pembangunan-unit')
                                <li>
                                    <a href="{{ route('produksi.pembangunanUnit.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'pembangunanUnit' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Pemb. Unit
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                    @endcanany

                    <!-- Pembangunan Kawasan -->
                    @canany(['produksi.buat-pembangunan-kawasan', 'produksi.pembangunan-kawasan'])
                    <li>
                        <a href="#" @click.prevent="selected = (selected === 'Kawasan' ? '':'Kawasan')"
                            class="menu-item group"
                            :class="(selected === 'Kawasan') || (page === 'buatPembangunanKawasan' || page === 'pembangunanKawasan') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg :class="(selected === 'Kawasan') || (page === 'buatPembangunanKawasan' || page === 'pembangunanKawasan') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                class="w-6 h-6 size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Kawasan</span>
                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'Kawasan') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '']"
                                width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                        <div class="overflow-hidden transform translate" :class="(selected === 'Kawasan') ? 'block' : 'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                @can('produksi.buat-pembangunan-kawasan')
                                <li>
                                    <a href="{{ route('produksi.buatPembangunanKawasan.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'buatPembangunanKawasan' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Buat Pemb.
                                    </a>
                                </li>
                                @endcan
                                @can('produksi.pembangunan-kawasan')
                                <li>
                                    <a href="{{ route('produksi.pembangunanKawasan.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'pembangunanKawasan' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Pemb. Kawasan
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                    @endcanany

                    <!-- Proyek Kontraktor -->
                    @canany(['produksi.project-baru', 'produksi.pembangunan-proyek'])
                    <li>
                        <a href="#" @click.prevent="selected = (selected === 'Kontraktor' ? '':'Kontraktor')"
                            class="menu-item group"
                            :class="(selected === 'Kontraktor') || (page === 'projectBaru' || page === 'pembangunanProyek') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg :class="(selected === 'Kontraktor') || (page === 'projectBaru' || page === 'pembangunanProyek') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                class="w-6 h-6 size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"></path>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Kontraktor</span>
                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'Kontraktor') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '']"
                                width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                        <div class="overflow-hidden transform translate" :class="(selected === 'Kontraktor') ? 'block' : 'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                @can('produksi.project-baru')
                                <li>
                                    <a href="{{ route('produksi.projectBaru.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'projectBaru' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Project Baru
                                    </a>
                                </li>
                                @endcan
                                @can('produksi.pembangunan-proyek')
                                <li>
                                    <a href="{{ route('produksi.pembangunanProyek.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'pembangunanProyek' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Pemb. Proyek
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                    @endcanany


                    <!-- Manajemen Upah -->
                    @canany(['produksi.penamaan-upah', 'produksi.upah-properti', 'produksi.upah-kontraktor', 'produksi.upah-kawasan'])
                    <li>
                        <a href="#" @click.prevent="selected = (selected === 'ManajemenUpah' ? '':'ManajemenUpah')"
                            class="menu-item group"
                            :class="(selected === 'ManajemenUpah') || (page === 'PenamaanUpah' || page === 'persetujuanUpah' || page === 'persetujuanUpahKontraktor' || page === 'persetujuanUpahKawasan') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg :class="(selected === 'ManajemenUpah') || (page === 'PenamaanUpah' || page === 'persetujuanUpah' || page === 'persetujuanUpahKontraktor' || page === 'persetujuanUpahKawasan') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                class="w-6 h-6 size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"></path>
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Manajemen Upah</span>
                            <svg class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'ManajemenUpah') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '']"
                                width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                        <div class="overflow-hidden transform translate" :class="(selected === 'ManajemenUpah') ? 'block' : 'hidden'">
                            <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                @can('produksi.penamaan-upah')
                                <li>
                                    <a href="{{ route('produksi.masterUpah.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'PenamaanUpah' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Penamaan Upah
                                    </a>
                                </li>
                                @endcan
                                @can('produksi.upah-properti')
                                <li>
                                    <a href="{{ route('produksi.persetujuanUpahProperti.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'persetujuanUpah' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Upah Pemb. Unit
                                    </a>
                                </li>
                                @endcan
                                {{-- 
                                @can('produksi.upah-kawasan')
                                <li>
                                    <a href="{{ route('produksi.persetujuanUpahKawasan.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'persetujuanUpahKawasan' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Upah Pemb. Kawasan
                                    </a>
                                </li>
                                @endcan
                                @can('produksi.upah-kontraktor')
                                <li>
                                    <a href="{{ route('produksi.persetujuanUpahKontraktor.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'persetujuanUpahKontraktor' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Upah Pemb. Proyek
                                    </a>
                                </li>
                                @endcan
                                --}}
                            </ul>
                        </div>
                    </li>
                    @endcanany
                </ul>
            </div>
            @endcanany

             @canany(['kpi.master-kpi.read', 'kpi.kpi-user.read', 'kpi.kpi-riview.read'])
                <div>
                    <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                            Kpi
                        </span>

                        <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                            class="mx-auto fill-current menu-group-icon" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                fill="" />
                        </svg>
                    </h3>


                    <ul class="flex flex-col gap-2 mb-6">
                        @can('kpi.kpi-user.read')
                            <li>
                                <a href="{{ route('kpi.dashboard.index') }}"
                                    @click="selected = (selected === 'Dashboard-KPI' ? '':'Dashboard-KPI')" class="menu-item group"
                                    :class="(selected === 'Dashboard-KPI' || page === 'Dashboard-KPI') ? 'menu-item-active' :
                                    'menu-item-inactive'">

                                    <svg :class="(selected === 'Dashboard-KPI' || page === 'Dashboard-KPI') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                        class="w-6 h-6 size-6" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                                    </svg>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Dashboard
                                    </span>
                                </a>
                            </li>
                        @endcan

                        @can('kpi.master-kpi.read')
                            <li>
                                <a href="{{ route('kpi.komponen.index') }}"
                                    @click="selected = (selected === 'Master-KPI' ? '':'Master-KPI')" class="menu-item group"
                                    :class="(selected === 'Master-KPI' || page === 'Master-KPI') ? 'menu-item-active' :
                                    'menu-item-inactive'">

                                    <svg :class="(selected === 'Master-KPI' || page === 'Master-KPI') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                        class="w-6 h-6 size-6" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        Master KPI
                                    </span>
                                </a>
                            </li>
                        @endcan

                        @can('kpi.kpi-user.read')
                            <li>
                                <a href="{{ route('kpi.user.index') }}"
                                    @click="selected = (selected === 'User-KPI' ? '':'User-KPI')" class="menu-item group"
                                    :class="(selected === 'User-KPI' || page === 'User-KPI') ? 'menu-item-active' :
                                    'menu-item-inactive'">

                                    <svg :class="(selected === 'User-KPI' || page === 'User-KPI') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                        class="w-6 h-6 size-6" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>


                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        KPI User
                                    </span>
                                </a>
                            </li>
                        @endcan

                        @can('kpi.kpi-riview.read')
                            <li>
                                <a href="{{ route('kpi.review.index') }}"
                                    @click="selected = (selected === 'Review-KPI' ? '':'Review-KPI')" class="menu-item group"
                                    :class="(selected === 'Review-KPI' || page === 'Review-KPI') ? 'menu-item-active' :
                                    'menu-item-inactive'">

                                    <svg :class="(selected === 'Review-KPI' || page === 'Review-KPI') ? 'text-brand-500 dark:text-brand-400' : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300'"
                                        class="w-6 h-6 size-6" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                                    </svg>


                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                        KPI Review
                                    </span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            @endcanany

            @canany(['superadmin.role.read', 'superadmin.akun-karyawan.read'])
                <!-- HR Management - Group -->
                <div>
                    <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                            HR Management
                        </span>

                        <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                            class="mx-auto fill-current menu-group-icon" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                fill="" />
                        </svg>
                    </h3>

                    <ul class="flex flex-col gap-2 mb-6">
                        <!-- Menu Item Karyawan -->
                        <li>
                            <a href="{{ route('superadmin.karyawan.index') }}"
                                @click="selected = (selected === 'karyawan' ? '':'karyawan')"
                                class="menu-item group"
                                :class="(selected === 'karyawan') && (page === 'karyawan') ? 'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'karyawan') && (page === 'karyawan') ? 'menu-item-icon-active' :
                                ''"
                                    width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" stroke="currentColor" stroke-width="1.5"
                                    class="size-6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm14 10v-2a4 4 0 0 0-3-3.87m-4-12a4 4 0 1 1 0 8" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Karyawan
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endcanany

            @canany(['superadmin.role.read', 'superadmin.akun-karyawan.read'])
                <!-- Superadmin - Group -->
                <div>
                    <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                            Superadmin
                        </span>

                        <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                            class="mx-auto fill-current menu-group-icon" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                fill="" />
                        </svg>
                    </h3>

                    <ul class="flex flex-col gap-2 mb-6">
                        <!-- Menu Item Role & Hak Akses -->
                        <li>
                            <a href="{{ route('superadmin.roleHakAkses.index') }}"
                                @click="selected = (selected === 'RoleHakAkses' ? '' : 'RoleHakAkses')"
                                class="menu-item group"
                                :class="(selected === 'RoleHakAkses') && (page === 'RoleHakAkses') ?
                                'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'RoleHakAkses') && (page === 'RoleHakAkses') ?
                                'menu-item-icon-active' :
                                ''"
                                    width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" stroke="currentColor" stroke-width="1.5" class="size-6">

                                    <!-- ICON ACCESS / ROLE -->
                                    <g fill="none" stroke="currentColor">
                                        <path stroke-linejoin="round"
                                            d="M7 2a5 5 0 0 0-2.5 9.331v6.512c0 .818 0 1.226.152 1.594c.152.367.442.656 1.02 1.235L7 22l2.108-2.108c.097-.097.146-.146.186-.199a1 1 0 0 0 .197-.475c.009-.066.009-.135.009-.272c0-.111 0-.167-.006-.221a1 1 0 0 0-.134-.401a2 2 0 0 0-.128-.18L8 16.5l.7-.933c.396-.529.595-.793.697-1.101c.103-.308.103-.638.103-1.3v-1.835A5 5 0 0 0 7 2Z" />

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.009" />

                                        <path stroke-linecap="round"
                                            d="M13 14h6c.932 0 1.398 0 1.765.152a2 2 0 0 1 1.083 1.083C22 15.602 22 16.068 22 17s0 1.398-.152 1.765a2 2 0 0 1-1.083 1.083C20.398 20 19.932 20 19 20h-6m2-15h4c.932 0 1.398 0 1.765.152a2 2 0 0 1 1.083 1.083C22 6.602 22 7.068 22 8s0 1.398-.152 1.765a2 2 0 0 1-1.083 1.083C20.398 11 19.932 11 19 11h-4" />
                                    </g>
                                </svg>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Role & Hak Akses
                                </span>
                            </a>
                        </li>
                        <!-- Menu Item Role & Hak Akses -->

                         <!-- Menu Item Akun Karyawan -->
                        <li>
                            <a href="{{ route('superadmin.akunKaryawan.index') }}"
                                @click="selected = (selected === 'akunKaryawan' ? '':'akunKaryawan')"
                                class="menu-item group"
                                :class="(selected === 'akunKaryawan') && (page === 'akunKaryawan') ? 'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'akunKaryawan') && (page === 'akunKaryawan') ?
                                'menu-item-icon-active' :
                                ''"
                                    width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    class="size-6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                        d="M4.5 17H4a1 1 0 0 1-1-1 3 3 0 0 1 3-3h1m0-3.05A2.5 2.5 0 1 1 9 5.5M19.5 17h.5a1 1 0 0 0 1-1 3 3 0 0 0-3-3h-1m0-3.05a2.5 2.5 0 1 0-2-4.45m.5 13.5h-7a1 1 0 0 1-1-1 3 3 0 0 1 3-3h3a3 3 0 0 1 3 3 1 1 0 0 1-1 1Zm-1-9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Akun Karyawan
                                </span>
                            </a>
                        </li>
                        <!-- Menu Item Akun Karyawan -->

                        <!-- Menu Item Devisi -->
                        <li>
                            <a href="{{ route('superadmin.devisi.index') }}"
                                @click="selected = (selected === 'devisi' ? '':'devisi')"
                                class="menu-item group"
                                :class="(selected === 'devisi') && (page === 'devisi') ? 'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'devisi') && (page === 'devisi') ? 'menu-item-icon-active' :
                                ''"
                                    width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" stroke="currentColor" stroke-width="1.5" class="size-6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Devisi
                                </span>
                            </a>
                        </li>
                        <!-- Menu Item Devisi -->
                    </ul>
                </div>
            @endcanany
        </nav>
        <!-- Sidebar Menu -->
    </div>
</aside>
