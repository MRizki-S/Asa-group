📦 *PENGAJUAN DRAFT NOTA BARANG MASUK*

Terdapat pengajuan draft nota barang masuk baru dengan rincian berikut:

• *Tanggal Nota:* {{ $nota->tanggal_nota ? \Carbon\Carbon::parse($nota->tanggal_nota)->format('d/m/Y') : '-' }}
• *Supplier:* {{ $nota->supplier->nama_supplier ?? '-' }}
• *Gudang / UBS:* {{ $nota->ubs->nama_ubs ?? '-' }}
• *Cara Bayar:* {{ ucfirst($nota->cara_bayar ?? '-') }}
• *Diajukan Oleh:* {{ $nota->creator->name ?? (auth()->user()->name ?? '-') }}
• *Waktu Pengajuan:* {{ $nota->created_at ? \Carbon\Carbon::parse($nota->created_at)->format('d/m/Y H:i') . ' WIB' : now()->format('d/m/Y H:i') . ' WIB' }}

*Daftar Barang:*
@foreach($nota->details as $idx => $item)
{{ $idx + 1 }}. *{{ $item->barang->nama_barang ?? '-' }}*@if(!empty($item->barang->kode_barang)) ({{ $item->barang->kode_barang }})@endif

   • Jumlah: {{ (float)$item->jumlah_input }} {{ $item->satuan->nama ?? '-' }}
@if(!empty($item->merk))   • Merk: {{ $item->merk }}
@endif   • Harga Satuan: Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
   • Total: Rp {{ number_format($item->harga_total, 0, ',', '.') }}
@endforeach

*Total Overall:* Rp {{ number_format($nota->details->sum('harga_total'), 0, ',', '.') }}
*Status:* ⏳ *Draft (Menunggu Tinjauan / Posting)*
