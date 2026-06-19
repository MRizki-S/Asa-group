<?php

$dirSrc = __DIR__ . '/resources/views/produksi/persetujuan-upah/index.blade.php';
$content = file_get_contents($dirSrc);

$views = [
    'persetujuan_upah_properti' => [
        'title' => 'Persetujuan Upah Properti',
        'route' => 'produksi.persetujuanUpahProperti',
        'table1' => '{{ $item->pembangunanUnit->unit->nama_unit ?? \'-\' }}',
        'table2' => '{{ $item->nama_upah }}',
        'table3' => '{{ $item->pembangunanUnitQc->qcContainer->nama_container ?? \'-\' }}'
    ],
    'persetujuan_upah_kontraktor' => [
        'title' => 'Persetujuan Upah Kontraktor',
        'route' => 'produksi.persetujuanUpahKontraktor',
        'table1' => '{{ $item->proyek->nama ?? \'-\' }}',
        'table2' => '{{ $item->nama_upah }}',
        'table3' => '-'
    ],
    'persetujuan_upah_kawasan' => [
        'title' => 'Persetujuan Upah Kawasan',
        'route' => 'produksi.persetujuanUpahKawasan',
        'table1' => '{{ $item->kawasan->nama ?? \'-\' }}',
        'table2' => '{{ $item->nama_upah }}',
        'table3' => '-'
    ]
];

foreach ($views as $folder => $config) {
    $newContent = str_replace(
        ['Persetujuan Upah', 'route(\'produksi.persetujuanUpah.index\')', '/produksi/persetujuan-upah/'],
        [$config['title'], "route('{$config['route']}.index')", "/produksi/{$folder}/"],
        $content
    );
    
    // Replace table row contents
    $newContent = str_replace('{{ $item->pembangunanUnit->unit->nama_unit ?? \'-\' }}', $config['table1'], $newContent);
    $newContent = str_replace('{{ $item->nama_upah }}', $config['table2'], $newContent);
    $newContent = str_replace('{{ $item->pembangunanUnitQc->qcContainer->nama_container ?? \'-\' }}', $config['table3'], $newContent);

    // Save
    file_put_contents(__DIR__ . "/resources/views/produksi/{$folder}/index.blade.php", $newContent);
    echo "Created {$folder}/index.blade.php\n";
}
