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

<td style="background:{{ $hex }};min-width:100px;min-height:100px;color:#fff;font-family:arial;font-size:16px;">
    {{ $color }}<br>{{ $avgTemp }}&deg;
</td>