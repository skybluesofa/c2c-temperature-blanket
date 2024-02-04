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

<div class="min-h-24 p-1 rounded text-stone-200" style="background:{{ $hex }}">
    {{ $color }}<br>{{ $hours }} hrs
</div>
