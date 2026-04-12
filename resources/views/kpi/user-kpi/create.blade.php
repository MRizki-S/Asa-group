@extends('layouts.app')

@section('pageActive', 'User-KPI')

@section('content')
    <div class="mx-auto max-w-[--breakpoint-2xl] p-4 md:p-6" x-data="kpiCreateHandler()">

        <div x-data="{ pageName: 'Inisialisasi Penilaian KPI' }">
            @include('partials.breadcrumb')
        </div>

        {{-- Alert Error Server Side --}}
        @if ($errors->any())
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                role="alert">
                <ul class="list-disc list-inside text-xs font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('kpi.user.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6">

                {{-- Form Periode & Role --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Periode & Target</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-gray-800 dark:text-white/90">Pilih
                                Role</label>
                            <select name="role_id" x-model="selectedRole" @change="fetchData()"
                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-900 py-3 px-5 outline-none focus:border-blue-600 dark:border-gray-700 text-gray-700 dark:text-white/80 transition">
                                <option value="">-- Pilih Role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-gray-800 dark:text-white/90">Bulan</label>
                            <select name="bulan"
                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-900 py-3 px-5 outline-none focus:border-blue-600 dark:border-gray-700 text-gray-700 dark:text-white/80 transition">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ date('m') == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-gray-800 dark:text-white/90">Tahun</label>
                            <select name="tahun"
                                class="w-full rounded-lg border border-gray-300 bg-white dark:bg-gray-900 py-3 px-5 outline-none focus:border-blue-600 dark:border-gray-700 text-gray-700 dark:text-white/80 transition">
                                @for ($y = date('Y'); $y >= date('Y') - 2; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="selectedRole != ''" x-transition>

                    {{-- List User --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Pilih Karyawan</h3>
                            <label
                                class="flex items-center gap-2 cursor-pointer text-sm font-medium text-blue-600 hover:text-blue-700 transition">
                                <input type="checkbox" @click="toggleAllUsers()" x-bind:checked="allUsersSelected"
                                    class="w-4 h-4 rounded border-gray-300 focus:ring-blue-500">
                                Select All
                            </label>
                        </div>
                        <div class="max-h-80 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                            <template x-for="user in users" :key="user.id">
                                <label
                                    class="flex items-center p-3 rounded-xl border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] cursor-pointer transition">
                                    <input type="checkbox" name="user_ids[]" :value="user.id" x-model="selectedUsers"
                                        class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300 font-medium"
                                        x-text="user.nama_lengkap"></span>
                                </label>
                            </template>
                            <div x-show="users.length === 0" class="text-sm text-gray-400 italic py-4 text-center">Tidak ada
                                user dengan role ini.</div>
                        </div>
                    </div>

                    {{-- List Komponen & Bobot --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Komponen & Bobot</h3>
                            <div class="text-right text-sm">
                                <span class="text-gray-500 font-medium text-xs uppercase">Total: </span>
                                <span :class="totalBobot == 100 ? 'text-green-600' : 'text-red-600'"
                                    class="font-bold text-lg transition-colors" x-text="totalBobot + '%'"></span>
                            </div>
                        </div>
                        <div class="max-h-80 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                            <template x-for="comp in komponen" :key="comp.id">
                                <div
                                    class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                                    <input type="checkbox" name="komponen_ids[]" :value="comp.id"
                                        x-model="selectedKomponen" @change="recalculateWeights()"
                                        class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">

                                    <div class="flex-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                            x-text="comp.nama_komponen"></span>
                                    </div>

                                    <div class="w-24">
                                        <div class="relative">
                                            <input type="number" :name="'bobot[' + comp.id + ']'"
                                                x-model.number="weights[comp.id]" @input="updateTotal()"
                                                :disabled="!selectedKomponen.includes(comp.id.toString())"
                                                class="w-full pl-3 pr-8 py-1.5 text-sm font-bold text-gray-700 dark:text-white bg-gray-50 dark:bg-gray-800 border-none rounded-lg focus:ring-2 focus:ring-blue-600 disabled:opacity-30 transition"
                                                placeholder="0">
                                            <span class="absolute right-3 top-1.5 text-xs text-gray-400 font-bold">%</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="komponen.length === 0" class="text-sm text-gray-400 italic py-4 text-center">Role
                                ini belum memiliki Master Komponen.</div>
                        </div>

                        {{-- Error Message --}}
                        <div x-show="totalBobot != 100 && selectedKomponen.length > 0"
                            class="mt-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/10 text-xs text-red-500 font-bold italic flex items-center gap-2 transition">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Total bobot harus tepat 100% agar dapat disimpan.</span>
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="flex justify-end items-center gap-3 mt-4">
                    <a href="{{ route('kpi.user.index') }}"
                        class="px-6 py-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 transition">
                        Batal
                    </a>

                    <button type="submit"
                        :disabled="selectedUsers.length === 0 || selectedKomponen.length === 0 || totalBobot != 100"
                        class="px-10 py-3 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Simpan & Inisialisasi
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function kpiCreateHandler() {
            return {
                selectedRole: '',
                users: [],
                komponen: [],
                selectedUsers: [],
                selectedKomponen: [],
                weights: {},
                totalBobot: 0,
                allUsersSelected: true,

                async fetchData() {
                    if (!this.selectedRole) return;

                    try {
                        const response = await fetch(`/kpi-user/get-role-data/${this.selectedRole}`);
                        const data = await response.json();

                        this.users = data.users;
                        this.komponen = data.komponen;

                        this.selectedUsers = this.users.map(u => u.id.toString());
                        this.allUsersSelected = true;

                        this.selectedKomponen = this.komponen.map(c => c.id.toString());
                        this.recalculateWeights();

                    } catch (error) {
                        console.error("Gagal mengambil data:", error);
                        alert("Gagal mengambil data role. Silakan coba lagi.");
                    }
                },

                toggleAllUsers() {
                    if (this.allUsersSelected) {
                        this.selectedUsers = [];
                        this.allUsersSelected = false;
                    } else {
                        this.selectedUsers = this.users.map(u => u.id.toString());
                        this.allUsersSelected = true;
                    }
                },

                recalculateWeights() {
                    const count = this.selectedKomponen.length;

                    this.weights = {};
                    this.komponen.forEach(c => this.weights[c.id] = 0);

                    if (count > 0) {
                        if (count === 3) {
                            const pattern = [50, 30, 20];
                            this.selectedKomponen.forEach((id, index) => {
                                this.weights[id] = pattern[index];
                            });
                        } else {
                            const avg = Math.floor(100 / count);
                            const remainder = 100 % count;

                            this.selectedKomponen.forEach((id, index) => {
                                this.weights[id] = (index === 0) ? (avg + remainder) : avg;
                            });
                        }
                    }

                    this.updateTotal();
                },
                updateTotal() {
                    let sum = 0;
                    this.selectedKomponen.forEach(id => {
                        sum += parseInt(this.weights[id] || 0);
                    });
                    this.totalBobot = sum;
                }
            }
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #E5E7EB;
            border-radius: 10px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #374151;
        }

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
@endsection
