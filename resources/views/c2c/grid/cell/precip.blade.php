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

<div class="min-h-24 p-1 rounded text-stone-200" style="background:{{ $hex }}">
    {{ $color }}<br>{{ $precipitation }}" {{ $title }}
</div>
