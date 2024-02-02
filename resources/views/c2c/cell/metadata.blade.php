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

<td colspan={{ $tileWidth }} style="background:#000;height:100px;color:#fff;font-family:arial;font-size:16px;">
    {{ $date->format('m/d/Y') }}<br>Row: {{ $row }}; Column: {{ $column }}
</td>
