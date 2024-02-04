@php
$hex = '#000';
$color = 'No Data';
$avgTemp = 'n/a';

krsort($colorInformation['temperature']);
if (!empty($positionInformation['weather']) && !is_null($positionInformation['weather']['temp']['avg'])) {
    $avgTemp = $positionInformation['weather']['temp']['avg'];
    foreach ($colorInformation['temperature'] as $tempLimit => $values) {
        if ((float) $positionInformation['weather']['temp']['avg'] > (float) $tempLimit) {
            $hex = $values[0];
            $color = $values[1];
            break;
        }
    }
}
@endphp

<div class="min-h-24 p-1 rounded text-stone-200" style="background:{{ $hex }}">
    {{ $color }}<br>{{ $avgTemp }}&deg;
</div>
