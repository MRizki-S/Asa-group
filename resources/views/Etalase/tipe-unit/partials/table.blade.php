@foreach ($tipeUnits as $item)
    <tr class="border-b border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
        <td class="px-6 py-4">{{ $item->nama_type }}</td>
        <td class="px-6 py-4 text-center">
            <span
                class="px-3 py-1 rounded-full text-sm font-medium
            {{ $item->perumahaan->nama_perumahaan == 'Asa Dreamland'
                ? 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-200'
                : ($item->perumahaan->nama_perumahaan == 'Lembah Hijau Residence'
                    ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200'
                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300') }}">
                {{ $item->perumahaan->nama_perumahaan ?? '-' }}
            </span>
        </td>
        <td class="px-6 py-4">{{ number_format($item->luas_bangunan, 0, ',', '.') }} m²</td>
        <td class="px-6 py-4">{{ number_format($item->luas_tanah, 0, ',', '.') }} m²</td>
        <td class="px-6 py-4">{{ number_format($item->harga_dasar, 0, ',', '.') }}</td>
        <td class="px-6 py-4">
            {{ $item->harga_diajukan ? number_format($item->harga_diajukan, 0, ',', '.') : 'Tidak ada pengajuan' }}</td>
        <td class="px-6 py-4 text-center">
            @php
                $statusColor = match ($item->status_pengajuan) {
                    'acc' => 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200',
                    'tolak' => 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-200',
                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                };
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                {{ $item->status_pengajuan ?? 'Belum ada pengajuan' }}
            </span>
        </td>
        {{-- Kolom Aksi --}}
        <td class="px-6 py-4 flex flex-wrap gap-2 justify-center">
            <!-- Tombol Edit -->
            <!-- tombol Edit (dalam foreach baris tabel) -->
            <a href="#"
                class="btn-edit inline-flex items-center gap-1
                                    text-xs font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200
                                    dark:bg-yellow-800 dark:text-yellow-100 dark:hover:bg-yellow-700
                                    px-2.5 py-1.5 rounded-md transition-colors duration-200
                                    focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-1
                                    active:scale-95"
                data-modal-target="modal-edit-{{ $item->slug }}" data-modal-toggle="modal-edit-{{ $item->slug }}">
                Edit
            </a>

            {{-- Tombol Delete --}}
            <form action="{{ route('tipe-unit.destroy', $item->slug) }}" method="POST" class="inline delete-form">
                @csrf
                @method('DELETE')
                <button type="button"
                    class="delete-btn px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                    data-id="{{ $item->slug }}">
                    Delete
                </button>
            </form>
        </td>

    </tr>
@endforeach

