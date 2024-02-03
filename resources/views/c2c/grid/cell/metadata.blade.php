@use('Carbon\Carbon')
@php
$first = new Carbon('2023-12-31');

$tileWidth = count($meta['design'][0]);
$totalColumns = $meta['columns'];
$column = 1;
$row = 1;

$diff = $first->diffInDays($date);

$column = ($diff % $totalColumns) + 1;
$row = floor($diff / $totalColumns) + 1;
@endphp

<div class="bg-stone-900 p-4 min-h-24 text-white rounded flex justify-center">
    <div class="self-center text-center">
        <div class="text-lg text-stone-400 font-bold">{{ $date->format('m/d/Y') }}</div>
        <div class="text-stone-300">Row: {{ $row }}; Column: {{ $column }}</div>
    </div>
</div>
