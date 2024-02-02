@php
$hex = '#000';
$color = 'No Data';
$hours = 'n/a';

krsort($colorInformation['daylight']);
if (!empty($positionInformation['weather']) && !is_null($positionInformation['weather']['daylight'])) {
    $hours = $positionInformation['weather']['daylight'];
    foreach ($colorInformation['daylight'] as $light => $values) {
        if ($positionInformation['weather']['daylight'] > $light) {
            $hex = $values[0];
            $color = $values[1];
            break;
        }
    }
}
@endphp

<td style="background:{{ $hex }};min-width:100px;min-height:100px;color:#fff;font-family:arial;font-size:16px;">
    {{ $color }}<br>{{ $hours }} hrs
</td>