@php
$hex = '#000';
$color = 'No Data';
$title = 'Precip';
$precipitation = 0;
$precipitationType = 'n/a';

if (!empty($positionInformation['weather']) && is_array($positionInformation['weather']['precipitation'])) {

    $snowfall = $positionInformation['weather']['precipitation']['snow'];
    $rain = $positionInformation['weather']['precipitation']['rain'];
    $averageTemp = $positionInformation['weather']['temp']['avg'];

    $precipitation = $snowfall;
    $precipitationType = 'snow';

    if (($snowfall == $rain && $averageTemp >= 32) || $snowfall < $rain) {
        $precipitation = $rain;
        $precipitationType = 'rain';
    }
    $title = ucfirst($precipitationType);

    if (isset($colorInformation['precipitation'][$precipitationType])) {
        krsort($colorInformation['precipitation'][$precipitationType]);
        foreach ($colorInformation['precipitation'][$precipitationType] as $amount => $values) {
            if ($precipitation > $amount) {
                $hex = $values[0];
                $color = $values[1];
                break;
            }
        }
    }
}
@endphp

<td style="background:{{ $hex }};min-width:100px;min-height:100px;color:#fff;font-family:arial;font-size:16px;">
    {{ $color }}<br>{{ $precipitation }}" {{ $title }}
</td>