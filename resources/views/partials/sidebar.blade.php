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
                <span class="text-blue-500">Asa</span>
                <span class="text-black dark:text-white">Group</span>
            </span>
        </a>
    </div>

    <!-- SIDEBAR HEADER -->
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <!-- Sidebar Menu -->
        <nav x-data="{ selected: $persist('Dashboard') }">

            <!-- Menu Group - Dashboard -->
            <div>
                <ul class="flex flex-col gap-4 mb-6">
                    <li>
                        {{-- dahboard title --}}
                        <a href="/#" @click.prevent="selected = (selected === 'Dashboard' ? '':'Dashboard')"
                            class="menu-item group"
                            :class="(selected === 'Dashboard') || (page === 'Marketing' ||
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
                                <li>
                                    <a href="/dashboard-marketing" class="menu-dropdown-item group"
                                        :class="page === 'Dashboard-Marketing' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        Marketing
                                    </a>
                                </li>
                                {{-- Link menu Dashboard - Marketing --}}

                                {{-- Link menu Dashboard - Keuangan --}}
                                <li>
                                    <a href="index.html" class="menu-dropdown-item group"
                                        :class="page === 'Keuangan' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        Keuangan
                                    </a>
                                </li>
                                {{-- Link menu Dashboard - Keuangan --}}


                                {{-- Link menu Dashboard - Produksi --}}
                                <li>
                                    <a href="index.html" class="menu-dropdown-item group"
                                        :class="page === 'Produksi' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        Produksi
                                    </a>
                                </li>
                                {{-- Link menu Dashboard - Produksi --}}

                                {{-- Link menu Dashboard - Gudang --}}
                                <li>
                                    <a href="index.html" class="menu-dropdown-item group"
                                        :class="page === 'Gudang' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        Gudang
                                    </a>
                                </li>
                                {{-- Link menu Dashboard - Gudang --}}
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Dashboard -->
                </ul>
            </div>
            <!-- Menu Group - Dashboard -->

            {{-- @role(['Super Admin', 'Admin KPR']) --}}
            <!-- Menu Group - Etalase -->
            <div>
                <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
                    <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                        Etalase
                    </span>

                    <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                        class="mx-auto fill-current menu-group-icon" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                            fill="" />
                    </svg>
                </h3>

                <ul class="flex flex-col gap-2 mb-6">
                    <!-- Menu Item Perumahaan -->
                    <li>
                        <a href="/etalase/perumahaan" @click="selected = (selected === 'Perumahaan' ? '':'Perumahaan')"
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
                    <!-- Menu Item Perumahaan -->

                    <!-- Menu Item Tipe Unit -->
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
                    <!-- Menu Item Tipe Unit -->

                    <!-- Menu Item Kualifikasi Blok -->
                    <li>
                        <a href="/etalase/kualifikasi-blok"
                            @click="selected = (selected === 'KualifikasiBlok' ? '':'KualifikasiBlok')"
                            class="menu-item group"
                            :class="(selected === 'KualifikasiBlok') && (page === 'KualifikasiBlok') ? 'menu-item-active' :
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
                    <!-- Menu Item Kualifikasi Blok -->

                    <!-- Menu Item Blok - Unit -->
                    <li>
                        <a href="#" @click.prevent="selected = (selected === 'blokUnit' ? '':'blokUnit')"
                            class="menu-item group"
                            :class="(selected === 'blokUnit') || (page === 'blokLayout' || page === 'formLayout' ||
                                page === 'problokLayout' || page === 'proFormLayout') ? 'menu-item-active' :
                            'menu-item-inactive'">
                            <svg :class="(selected === 'blokUnit') || (page === 'blokLayout' || page === 'formLayout' ||
                                page === 'problokLayout' || page === 'proFormLayout') ? 'menu-item-icon-active' :
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
                                :class="[(selected === 'blokUnit') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive',
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
                                <li>
                                    <a href="{{ route('blok.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'blokLayout' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linejoin="round" stroke-width="1.2"
                                                d="M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5Zm16 14a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2ZM4 13a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6Zm16-2a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6Z" />
                                        </svg>
                                        Blok
                                    </a>
                                </li>
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
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item blokUnit -->

                    <!-- Menu Item Pengajuan Perubahaan Harga -->
                    <li>
                        <a href="#" @click.prevent="selected = (selected === 'PengajuanHarga' ? '':'PengajuanHarga')"
                            class="menu-item group"
                            :class="(selected === 'PengajuanHarga') || (page === 'PerubahaanHargaTipeUnit' || page === 'PerubahaanHargaTahapKualifikasiBlok') ? 'menu-item-active' :
                            'menu-item-inactive'">
                            <svg :class="(selected === 'PengajuanHarga') || (page === 'PerubahaanHargaTipeUnit' || page === 'PerubahaanHargaTahapKualifikasiBlok'
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
                                :class="[(selected === 'PengajuanHarga') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive',
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
                                <li>
                                    <a href="{{ route('perubahan-harga.tipe-unit.index') }}" class="menu-dropdown-item group"
                                        :class="page === 'PerubahanHargaTipeUnit' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg class="w-6 h-6 text-gray-800 dark:text-white"
                                            width="24" height="24" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                                        </svg>
                                        Harga Tipe Unit
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('unit.indexGlobal') }}" class="menu-dropdown-item group"
                                        :class="page === 'PerubahaanHargaTahapKualifikasiBlok' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        <svg class="w-6 h-6 text-gray-800 dark:text-white" width="24"
                                            height="24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                                        </svg>
                                        Harga   Tahap - Kualifikasi Blok
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Pengajuan Perubahaan Harga-->
                    
                </ul>
            </div>
            <!-- Menu Group - Etalase -->
            {{-- @endrole --}}



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
                    <li>
                        <a href="{{ route('marketing.akunUser.index') }}"
                            @click="selected = (selected === 'AkunUser' ? '':'AkunUser')" class="menu-item group"
                            :class="(selected === 'AkunUser') && (page === 'AkunUser') ? 'menu-item-active' :
                            'menu-item-inactive'">

                            <svg :class="(selected === 'AkunUser') && (page === 'AkunUser') ? 'menu-item-icon-active' :
                            ''"
                                width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                class="size-6">
                                <path fill-rule="evenodd"
                                    d="M7 2a2 2 0 0 0-2 2v1a1 1 0 0 0 0 2v1a1 1 0 0 0 0 2v1a1 1 0 1 0 0 2v1a1 1 0 1 0 0 2v1a1 1 0 1 0 0 2v1a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H7Zm3 8a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm-1 7a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3 1 1 0 0 1-1 1h-6a1 1 0 0 1-1-1Z"
                                    clip-rule="evenodd" />
                            </svg>


                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Akun User - Booking
                            </span>
                        </a>
                    </li>
                    <!-- Menu Item Akun User -->

                    @hasrole(['Super Admin', 'Admin KPR', 'Sales'])
                        <!-- Menu Item Pemesanan Unit -->
                        <li>
                            <a href="{{ route('marketing.pemesananUnit.index') }}"
                                @click="selected = (selected === 'PemesananUnit' ? '':'PemesananUnit')"
                                class="menu-item group"
                                :class="(selected === 'PemesananUnit') && (page === 'PemesananUnit') ? 'menu-item-active' :
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
                        <!-- Menu Item Pemesanan Unit -->
                    @endrole

                    <!-- Menu Item Manage Pemesanan -->
                    <li>
                        <a href="/marketing/manage-pemesanan"
                            @click="selected = (selected === 'ManagePemesanan' ? '':'ManagePemesanan')"
                            class="menu-item group"
                            :class="(selected === 'ManagePemesanan') && (page === 'ManagePemesanan') ? 'menu-item-active' :
                            'menu-item-inactive'">
                            <svg :class="(selected === 'ManagePemesanan') && (page === 'ManagePemesanan') ?
                            'menu-item-icon-active' :
                            'menu-item-icon-inactive'"
                                width="26" height="26" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="#ffffff" stroke-width="2.4" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-folder-kanban-icon lucide-folder-kanban">
                                <path
                                    d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z" />
                                <path d="M8 10v4" />
                                <path d="M12 10v2" />
                                <path d="M16 10v6" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Manage Pemesanan
                            </span>
                        </a>
                    </li>
                    <!-- Menu Item Manage Pemesanan -->

                    {{-- project manager yang bisa lihat ini dan bisa kontrol ini --}}
                    <!-- Menu Item Pengajuan -->
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
                                :class="[(selected === 'Pengajuan') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive',
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

                                {{-- Pengajuan Pembatalan --}}
                                <li>
                                    <a href="/marketing/pengajuan-pemesanan" class="menu-dropdown-item group"
                                        :class="page === 'PengajuanPemesanan' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        Pengajuan Pemesanan
                                    </a>
                                </li>

                                {{-- Pengajuan Pembatalan --}}
                                <li>
                                    <a href="/marketing/pengajuan-pembatalan" class="menu-dropdown-item group"
                                        :class="page === 'PengajuanPembatalan' ? 'menu-dropdown-item-active' :
                                            'menu-dropdown-item-inactive'">
                                        Pengajuan Pembatalan
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Pengajuan -->

                    @hasrole(['Super Admin', 'Admin KPR', 'Manager Keuangan', 'Project Manager'])
                        <!-- Menu Item Adendum -->
                        <li>
                            <a href="#" @click.prevent="selected = (selected === 'Adendum' ? '':'Adendum')"
                                class="menu-item group"
                                :class="(selected === 'Adendum') || (page === 'BuatAdendum' ||
                                    page === 'ListAdendum') ? 'menu-item-active' : 'menu-item-inactive'">
                                <svg :class="(selected === 'Adendum') || (page === 'BuatAdendum' || page === 'ListAdendum') ?
                                'menu-item-icon-active' :
                                'menu-item-icon'"
                                    class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"
                                    role="img">
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
                                'block' : 'hidden'">
                                <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                    class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">

                                    {{-- Buat Adendum --}}
                                    @hasrole(['Super Admin', 'Admin KPR'])
                                        <li>
                                            <a href="{{ route('marketing.adendum.index') }}" class="menu-dropdown-item group"
                                                :class="page === 'BuatAdendum' ? 'menu-dropdown-item-active' :
                                                    'menu-dropdown-item-inactive'">
                                                Buat Adendum
                                            </a>
                                        </li>
                                    @endhasrole

                                    {{-- List Adendum --}}
                                    <li>
                                        <a href="{{ route('marketing.adendum.list') }}" class="menu-dropdown-item group"
                                            :class="page === 'ListAdendum' ? 'menu-dropdown-item-active' :
                                                'menu-dropdown-item-inactive'">
                                            List Adendum
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <!-- Dropdown Menu End -->
                        </li>
                        <!-- Menu Item Adendum -->
                    @endhasrole

                    @hasrole(['Super Admin', 'Admin KPR', 'Manager Keuangan', 'Project Manager'])
                        <!-- Menu Item Setting PPJB -->
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
                                    height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
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
                        <!-- Menu Item Setting PPJB -->
                    @endhasrole
                </ul>
            </div>

            @can('akses-gudang')
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
                        <!-- Menu Item Stock Barang -->
                        <li>
                            <a href="{{ route('marketing.akunUser.index') }}"
                                @click="selected = (selected === 'StockBarang' ? '' : 'StockBarang')"
                                class="menu-item group"
                                :class="(selected === 'StockBarang') && (page === 'StockBarang') ?
                                'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'StockBarang') && (page === 'StockBarang') ? 'menu-item-icon-active' :
                                ''"
                                    class="w-6 h-6 text-gray-800 dark:text-white size-6" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <title>warehouse-1</title>
                                    <g fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M2 7.5V22h20V7.5L12 2z" />
                                        <path d="M6 16v6h6v-6zm6 0v6h6v-6zm-3-6v6h6v-6z" />
                                    </g>
                                </svg>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Stock Barang
                                </span>
                            </a>
                        </li>
                        <!-- Menu Item Stock Barang -->

                        <!-- Menu Item Master Barang -->
                        <li>
                            <a href="{{ route('marketing.akunUser.index') }}"
                                @click="selected = (selected === 'MasterBarang' ? '' : 'MasterBarang')"
                                class="menu-item group"
                                :class="(selected === 'MasterBarang') && (page === 'MasterBarang') ?
                                'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'MasterBarang') && (page === 'MasterBarang') ? 'menu-item-icon-active' :
                                ''"
                                    class="w-6 h-6 text-gray-800 dark:text-white size-6" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 256 256" fill="currentColor">
                                    <title>package-duotone</title>
                                    <g fill="currentColor">
                                        <path
                                            d="M128 129.09V232a8 8 0 0 1-3.84-1l-88-48.18a8 8 0 0 1-4.16-7V80.18a8 8 0 0 1 .7-3.25Z"
                                            opacity=".2" />
                                        <path
                                            d="m223.68 66.15l-88-48.15a15.88 15.88 0 0 0-15.36 0l-88 48.17a16 16 0 0 0-8.32 14v95.64a16 16 0 0 0 8.32 14l88 48.17a15.88 15.88 0 0 0 15.36 0l88-48.17a16 16 0 0 0 8.32-14V80.18a16 16 0 0 0-8.32-14.03M128 32l80.34 44l-29.77 16.3l-80.35-44Zm0 88L47.66 76l33.9-18.56l80.34 44ZM40 90l80 43.78v85.79l-80-43.75Zm176 85.78l-80 43.79v-85.75l32-17.51V152a8 8 0 0 0 16 0v-44.45L216 90v85.77Z" />
                                    </g>
                                </svg>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Master Barang
                                </span>
                            </a>
                        </li>
                        <!-- Menu Item Master Barang -->


                        <!-- Menu Item Daftar Nota Masuk -->
                        <li>
                            <a href="/marketing/manage-pemesanan"
                                @click="selected = (selected === 'ManagePemesanan' ? '' : 'ManagePemesanan')"
                                class="menu-item group"
                                :class="(selected === 'ManagePemesanan') && (page === 'ManagePemesanan') ?
                                'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'ManagePemesanan') && (page === 'ManagePemesanan') ?
                                'menu-item-icon-active' : ''"
                                    class="w-6 h-6 text-gray-800 dark:text-white size-6" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <title>receipt</title>
                                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2">
                                        <path stroke-linejoin="round"
                                            d="M19 21H7a4 4 0 0 1-4-4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v13c0 1.657.343 3 2 3" />
                                        <path stroke-linejoin="round"
                                            d="M21 10a2 2 0 0 0-2-2h-2v10.5c0 1.38.62 2.5 2 2.5s2-1.12 2-2.5z" />
                                        <path d="M13 11H7m6-4H7m3 8H7" />
                                    </g>
                                </svg>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Daftar Nota Masuk
                                </span>
                            </a>
                        </li>
                        <!-- Menu Item Daftar Nota Masuk -->

                        <!-- Menu Item Tambah Nota Masuk -->
                        <li>
                            <a href="/marketing/manage-pemesanan"
                                @click="selected = (selected === 'ManagePemesanan' ? '' : 'ManagePemesanan')"
                                class="menu-item group"
                                :class="(selected === 'ManagePemesanan') && (page === 'ManagePemesanan') ?
                                'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'ManagePemesanan') && (page === 'ManagePemesanan') ?
                                'menu-item-icon-active' : ''"
                                    class="w-6 h-6 text-gray-800 dark:text-white size-6" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <title>package-receive</title>
                                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5">
                                        <path stroke-linejoin="round" d="M12 22c-.818 0-1.6-.325-3.163-.974C4.946 19.41 3 18.602 3 17.243V7.745
                               M12 22c.818 0 1.6-.325 3.163-.974C19.054 19.41 21 18.602 21 17.243V7.745
                               M12 22v-9.831
                               M3 7.745c0 .603.802.985 2.405 1.747l2.92 1.39
                               C10.13 11.74 11.03 12.17 12 12.17
                               M3 7.745c0-.604.802-.986 2.405-1.748L7.5 5
                               M21 7.745c0 .603-.802.985-2.405 1.747l-2.92 1.39
                               C13.87 11.74 12.97 12.17 12 12.17
                               m9-4.424c0-.604-.802-.986-2.405-1.748L16.5 5
                               M6 13.152l2 .983" />
                                        <path d="M12.004 2v7m0 0c.263.004.522-.18.714-.405L14 7.062
                               M12.004 9c-.254-.003-.511-.186-.714-.405L10 7.062" />
                                    </g>
                                </svg>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Tambah Nota Masuk
                                </span>
                            </a>
                        </li>
                        <!-- Menu Item Tambah Nota Masuk -->



                    </ul>
                </div>
                <!-- Gudang -  Group -->
            @endcan

            @can('akses-produksi')
                <!-- Master Produksi RAP -->
                <div>
                    <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">
                            Master Produksi RAP
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
                        <!-- Menu Master QC & RAP-->
                        <li>
                            <a href="{{ route('marketing.akunUser.index') }}"
                                @click="selected = (selected === 'AkunUser' ? '':'AkunUser')" class="menu-item group"
                                :class="(selected === 'AkunUser') && (page === 'AkunUser') ? 'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'AkunUser') && (page === 'AkunUser') ? 'menu-item-icon-active' : ''"
                                    class="w-6 h-6 text-gray-800 dark:text-white size-6" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 48 48"
                                    fill="none">
                                    <g fill="currentColor">
                                        <path d="M26 6a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0v-1h-6V8h6V7a1 1 0 0 1 1-1" />
                                        <path
                                            d="M16 6a1 1 0 0 0-1 1v1h-2a3 3 0 0 0-3 3v24a3 3 0 0 0 3 3h17a3 3 0 0 0 3-3V11a3 3 0 0 0-3-3h-1v2h1a1 1 0 0 1 1 1v24a1 1 0 0 1-1 1H13a1 1 0 0 1-1-1V11a1 1 0 0 1 1-1h2v1a1 1 0 1 0 2 0V7a1 1 0 0 0-1-1" />
                                        <path
                                            d="M14 19a1 1 0 0 1 1-1h13a1 1 0 1 1 0 2H15a1 1 0 0 1-1-1m1 4a1 1 0 1 0 0 2h7a1 1 0 1 0 0-2zm-1 6a1 1 0 0 1 1-1h11a1 1 0 1 1 0 2H15a1 1 0 0 1-1-1" />
                                        <path d="M13 40a5 5 0 0 1-5-5V10H6v25a7 7 0 0 0 7 7h17v-2z" />
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M36 13a3 3 0 1 1 6 0v20.303l-3 4.5l-3-4.5zm3-1a1 1 0 0 0-1 1v2h2v-2a1 1 0 0 0-1-1m0 22.197l1-1.5V17h-2v15.697z" />
                                    </g>
                                </svg>




                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Master QC & RAP
                                </span>
                            </a>
                        </li>
                        <!-- Menu Master QC & RAP-->

                        <!-- Menu Penamaan Upah -->
                        <li>
                            <a href="{{ route('marketing.akunUser.index') }}"
                                @click="selected = (selected === 'PenamaanUpah' ? '':'PenamaanUpah')"
                                class="menu-item group"
                                :class="(selected === 'PenamaanUpah') && (page === 'PenamaanUpah') ? 'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'PenamaanUpah') && (page === 'PenamaanUpah') ? 'menu-item-icon-active' :
                                ''"
                                    class="w-6 h-6 text-gray-800 dark:text-white size-6" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <title>money-settings-24-regular</title>
                                    <path
                                        d="M10.5 8a3 3 0 1 0 0 6a3 3 0 0 0 0-6M9 11a1.5 1.5 0 1 1 3 0a1.5 1.5 0 0 1-3 0M2 7.25A2.25 2.25 0 0 1 4.25 5h12.5A2.25 2.25 0 0 1 19 7.25v3.924A6.5 6.5 0 0 0 17.5 11V9.5h-.75a2.25 2.25 0 0 1-2.25-2.25V6.5h-8v.75A2.25 2.25 0 0 1 4.25 9.5H3.5v3h.75a2.25 2.25 0 0 1 2.25 2.25v.75h4.813c-.154.478-.255.98-.294 1.5H4.25A2.25 2.25 0 0 1 2 14.75zM4.401 18.5h6.676c.08.523.223 1.026.421 1.5H7a3 3 0 0 1-2.599-1.5M20.5 11.732A6.5 6.5 0 0 1 22 12.81V10a3 3 0 0 0-1.5-2.599zM4.25 6.5a.75.75 0 0 0-.75.75V8h.75A.75.75 0 0 0 5 7.25V6.5zM17.5 8v-.75a.75.75 0 0 0-.75-.75H16v.75c0 .414.336.75.75.75zm-14 6.75c0 .414.336.75.75.75H5v-.75a.75.75 0 0 0-.75-.75H3.5zm10.778-.774a2 2 0 0 1-1.441 2.496l-.584.144a5.7 5.7 0 0 0 .006 1.808l.54.13a2 2 0 0 1 1.45 2.51l-.187.631c.44.386.94.699 1.484.922l.494-.519a2 2 0 0 1 2.899 0l.498.525a5.3 5.3 0 0 0 1.483-.913l-.198-.686a2 2 0 0 1 1.441-2.496l.584-.144a5.7 5 0 0 0-.006-1.808l-.54-.13a2 2 0 0 1-1.45-2.51l.187-.63a5.3 5.3 0 0 0-1.484-.922l-.493.518a2 2 0 0 1-2.9 0l-.498-.525a5.3 5.3 0 0 0-1.483.912zM17.5 19c-.8 0-1.45-.672-1.45-1.5S16.7 16 17.5 16s1.45.672 1.45 1.5S18.3 19 17.5 19" />
                                </svg>


                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Penamaan Upah
                                </span>
                            </a>
                        </li>
                        <!-- Menu Penamaan Upah -->
                    </ul>
                </div>
                <!-- Master Produksi RAP -->

                <!-- Produksi -  Group -->
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
                        <!-- Menu Item Unit Menunggu Pengawas-->
                        <li>
                            <a href="{{ route('marketing.akunUser.index') }}"
                                @click="selected = (selected === 'AkunUser' ? '':'AkunUser')" class="menu-item group"
                                :class="(selected === 'AkunUser') && (page === 'AkunUser') ? 'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'AkunUser') && (page === 'AkunUser') ? 'menu-item-icon-active' : ''"
                                    class="w-6 h-6 text-gray-800 dark:text-white size-6" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <title>home-person-20-regular</title>

                                    <path
                                        d="M8.998 2.388a1.5 1.5 0 0 1 2.005 0l5.5 4.942A1.5 1.5 0 0 1 17 8.445v.956a3 3 0 0 0-1-.36v-.596a.5.5 0 0 0-.166-.371l-5.5-4.942a.5.5 0 0 0-.668 0l-5.5 4.942A.5.5 0 0 0 4 8.445V15.5a.5.5 0 0 0 .5.5H7a.5.5 0 0 0 .5-.5V12A1.5 1.5 0 0 1 9 10.5h2a1.5 1.5 0 0 1 1.5 1.5v2.207a2.5 2.5 0 0 0-1 .792v-3a.5.5 0 0 0-.5-.5H9a.5.5 0 0 0-.5.5v3.5A1.5 1.5 0 0 1 7 17H4.5A1.5 1.5 0 0 1 3 15.5V8.446c0-.425.18-.83.498-1.115zM17.5 12a2 2 0 1 1-4 0a2 2 0 0 1 4 0m1.5 4.5c0 1.245-1 2.5-3.5 2.5S12 17.75 12 16.5a1.5 1.5 0 0 1 1.5-1.5h4a1.5 1.5 0 0 1 1.5 1.5" />
                                </svg>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Unit Menunggu Pengawas
                                </span>
                            </a>
                        </li>
                        <!-- Menu Item Unit Menunggu Pengawas-->


                        <!-- Menu Item Pembangunan Unit-->
                        <li>
                            <a href="{{ route('marketing.akunUser.index') }}"
                                @click="selected = (selected === 'AkunUser' ? '':'AkunUser')" class="menu-item group"
                                :class="(selected === 'AkunUser') && (page === 'AkunUser') ? 'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'PembangunanUnit') && (page === 'PembangunanUnit') ?
                                'menu-item-icon-active' : ''"
                                    class="w-6 h-6 text-gray-800 dark:text-white size-6" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <title>home-gear-line</title>
                                    <path
                                        d="M19 21H5a1 1 0 0 1-1-1v-9H1l10.327-9.388a1 1 0 0 1 1.346 0L23 11h-3v9a1 1 0 0 1-1 1M6 19h12V9.157l-6-5.454l-6 5.454zm2.591-5.191a3.5 3.5 0 0 1 0-1.622l-.991-.572l1-1.732l.991.573a3.5 3.5 0 0 1 1.404-.812V8.5h2v1.144c.532.159 1.01.44 1.404.812l.991-.573l1 1.731l-.991.573a3.5 3.5 0 0 1 0 1.622l.991.572l-1 1.731l-.991-.572a3.5 3.5 0 0 1-1.404.811v1.145h-2V16.35a3.5 3.5 0 0 1-1.404-.811l-.991.572l-1-1.73zm3.404.688a1.5 1.5 0 1 0 0-2.998a1.5 1.5 0 0 0 0 2.998" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Pembangunan Unit
                                </span>
                            </a>
                        </li>
                        <!-- Menu Item Pembangunan Unit-->


                        <!-- Menu Item Riwayat Pembangunan -->
                        <li>
                            <a href="/marketing/manage-pemesanan"
                                @click="selected = (selected === 'ManagePemesanan' ? '':'ManagePemesanan')"
                                class="menu-item group"
                                :class="(selected === 'ManagePemesanan') && (page === 'ManagePemesanan') ?
                                'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'HomeGroup') && (page === 'HomeGroup') ? 'menu-item-icon-active' : ''"
                                    class="w-6 h-6 text-gray-800 dark:text-white size-6" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <title>home-group</title>

                                    <path
                                        d="M17 16h-2v6h-3v-5H8v5H5v-6H3l7-6zM6 2l4 4H9v3H7V6H5v3H3V6H2zm12 1l5 5h-1v4h-3V9h-2v3h-1.66L14 10.87V8h-1z" />
                                </svg>

                                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                    Riwayat Pembangunan
                                </span>
                            </a>
                        </li>
                        <!-- Menu Item Riwayat Pembangunan -->
                    </ul>
                </div>
                <!-- Produksi -  Group -->
            @endcan

            @role('Super Admin')
                <!-- Superadmin -  Group -->
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
                        <!-- Menu Item Akun Karyawan -->
                        <li>
                            <a href="{{ route('marketing.akunUser.index') }}"
                                @click="selected = (selected === 'akunKaryawan' ? '':'akunKaryawan')"
                                class="menu-item group"
                                :class="(selected === 'akunKaryawan') && (page === 'akunKaryawan') ? 'menu-item-active' :
                                'menu-item-inactive'">

                                <svg :class="(selected === 'akunKaryawan') && (page === 'akunKaryawan') ? 'menu-item-icon-active' :
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

                        <!-- Menu Item Pemesanan Unit -->
                        {{-- <li>
                        <a href="" @click="selected = (selected === 'PemesananUnit' ? '':'PemesananUnit')"
                            class="menu-item group" :class="(selected === 'PemesananUnit') && (page === 'PemesananUnit') ? 'menu-item-active' :
                            'menu-item-inactive'">
                            <svg :class="(selected === 'PemesananUnit') && (page === 'PemesananUnit') ? 'menu-item-icon-active' :
                            ''" width="24" height="24" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
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
                    </li> --}}
                        <!-- Menu Item Pemesanan Unit -->
                    </ul>
                </div>
            @endrole
        </nav>
        <!-- Sidebar Menu -->
    </div>
</aside>
