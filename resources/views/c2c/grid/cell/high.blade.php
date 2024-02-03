@php
$hex = '#000';
$color = 'No Data';
$highTemp = 'n/a';

krsort($colorInformation['temperature']);
if (!empty($positionInformation['weather']) && !is_null($positionInformation['weather']['temp']['high'])) {
    $highTemp = $positionInformation['weather']['temp']['high'];
    foreach ($colorInformation['temperature'] as $temp => $values) {
        if ($positionInformation['weather']['temp']['high'] > $temp) {
            $hex = $values[0];
            $color = $values[1];
            break;
        }
    }
}
@endphp

<div class="min-h-24 p-1 rounded text-white" style="background:{{ $hex }};">
    {{ $color }}<br>{{ $highTemp }}&deg;
</div>
