@php
$hex = '#000';
$color = 'No Data';
$lowTemp = 'n/a';

krsort($colorInformation['temperature']);
if (!empty($positionInformation['weather']) && !is_null($positionInformation['weather']['temp']['low'])) {
    $lowTemp = $positionInformation['weather']['temp']['low'];
    foreach ($colorInformation['temperature'] as $temp => $values) {
        if ($positionInformation['weather']['temp']['low'] > $temp) {
            $hex = $values[0];
            $color = $values[1];
            break;
        }
    }
}
@endphp

<div class="min-h-24 p-1 rounded text-stone-200" style="background:{{ $hex }};">
    {{ $color }}<br>{{ $lowTemp }}&deg;
</div>
