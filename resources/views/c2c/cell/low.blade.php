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

<td style="background:{{ $hex }};min-width:100px;min-height:100px;color:#fff;font-family:arial;font-size:16px;">
    {{ $color }}<br>{{ $lowTemp }}&deg;
</td>