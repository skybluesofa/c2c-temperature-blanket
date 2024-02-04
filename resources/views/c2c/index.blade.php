<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ App\Facades\TemperatureBlanketConfig::get('year') }} Temperature Blanket</title>
        
        <link rel="icon" href="favicon.svg">
        <link rel="mask-icon" href="favicon.svg" color="#fff">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>
    <body class="antialiased bg-stone-800">
        <div class="fixed top-0 left-0 right-0 bg-stone-900 p-8 flex flex-row h-24">
            <h1 class="text-orange-800 pl-12 text-2xl font-bold w-1/3"><a href="/">{{ App\Facades\TemperatureBlanketConfig::get('year') }} C2C Temperature Blanket</a></h1>
            <h2 class="text-orange-300 text-center text-xl w-2/3">
                <a href="/?date={{ $info['rows']['current']['date']->clone()->subday()->format('m/d/Y') }}" class="text-orange-700 hover:text-stone-900 hover:bg-orange-700 p-1"> &lt; </a>
                <a href="/?date={{ $info['rows']['current']['date']->format('m/d/Y') }}" class="hover:text-stone-900 hover:bg-orange-300 p-1"> {{ $info['rows']['current']['date']->format('m/d/Y') }}  </a>
                <a href="/?date={{ $info['rows']['current']['date']->clone()->addday()->format('m/d/Y') }}" class="text-orange-700 hover:text-stone-900 hover:bg-orange-700 p-1"> &gt; </a>
            </h2>
        </div>

        <div class="fixed top-0 left-0 h-full bg-stone-950 w-14 group hover:w-96 z-50 transition-all text-stone-300 p-2 text-lrg">
            <div class="mt-28 hover:bg-stone-500 p-2 rounded">
                <div class="overflow-clip flex group-hover:w-80 group">
                    <i class="bi bi-calendar3 text-2xl" title="Year"></i><div class="hidden group-hover:inline pl-2 pt-1">Year</div>
                    <div class="bg-red-500 w-full h-96 hidden group-hover:block">more info</div>
                </div>
            </div>
            <div class="mt-2 hover:bg-stone-500 p-2 rounded">
                <div class="overflow-clip flex group-hover:w-80">
                    <i class="bi bi-geo text-2xl" title="Location"></i><div class="hidden group-hover:inline pl-2 pt-1">Location</div>
                </div>
            </div>
            <div class="mt-2 hover:bg-stone-500 p-2 rounded">
                <div class="overflow-clip flex group-hover:w-80">
                    <i class="bi bi-cloud-sleet text-2xl" title="Precipitation"></i><div class="hidden group-hover:inline pl-2 pt-1">Precipitation</div>
                </div>
            </div>
            <div class="mt-2 hover:bg-stone-500 p-2 rounded">
                <div class="overflow-clip flex group-hover:w-80">
                    <i class="bi bi-sun text-2xl" title="Daylight"></i><div class="hidden group-hover:inline pl-2 pt-1">Daylight</div>
                </div>
            </div>
            <div class="mt-2 hover:bg-stone-500 p-2 rounded">
                <div class="overflow-clip flex group-hover:w-80">
                    <i class="bi bi-thermometer text-2xl" title="Temperature"></i><div class="hidden group-hover:inline pl-2 pt-1">Temperature</div>
                </div>
            </div>
            <div class="mt-2 hover:bg-stone-500 p-2 rounded">
                <div class="overflow-clip flex group-hover:w-80">
                    <i class="bi bi-grid text-2xl" title="Tile Design"></i><div class="hidden group-hover:inline pl-2 pt-1">Tile Design</div>
                </div>
            </div>
            <div class="mt-2 hover:bg-stone-500 p-2 rounded">
                <div class="overflow-clip flex group-hover:w-80">
                    <i class="bi bi-columns text-2xl" title="Blanket Design"></i><div class="hidden group-hover:inline pl-2 pt-1">Blanket Design</div>
                </div>
            </div>
            <div class="mt-2 hover:bg-stone-500 p-2 rounded">
                <div class="overflow-clip flex group-hover:w-80">
                    <i class="bi bi-box-arrow-down text-2xl" title="Copy Design URL"></i><div class="hidden group-hover:inline pl-2 pt-1">Copy Design URL</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 p-8 pt-32 pl-20">
            @include('c2c.grid.cell.metadata', [
                        'meta' => $info['meta'],
                        'date' => $info['rows']['current']['cells']['previous']['date'],
                    ])
            @include('c2c.grid.cell.metadata', [
                        'meta' => $info['meta'],
                        'date' => $info['rows']['current']['cells']['current']['date'],
                    ])
            @include('c2c.grid.cell.metadata', [
                        'meta' => $info['meta'],
                        'date' => $info['rows']['current']['cells']['next']['date'],
                    ])

            @foreach ($info['rows']['current']['cells'] as $position => $positionInformation)
                <div class="grid grid-cols-4 gap-1">
                    @foreach ($info['meta']['design'] as $rows)
                        @foreach ($rows as $cellDesign)
                            @include('c2c.grid.cell', [
                                        'cellDesign' => $cellDesign,
                                        'positionInformation' => $positionInformation,
                                        'colorInformation' => $info['meta']['colors'],
                                    ])
                        @endforeach
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="text-stone-500 fixed bottom-4 left-0 right-0 text-sm text-center">Data cached on {{ $info['meta']['cachedDate'] }}</div>

        <script>
            document.onkeydown = checkKey;

            function checkKey(e) {
                e = e || window.event;
                if (e.keyCode == "38") {
                    document.location.href="/?date={{ $info['rows']['previous']['date']->format('m/d/Y') }}";
                }
                else if (e.keyCode == "37") {
                    document.location.href="/?date={{ $info['rows']['current']['cells']['previous']['date']->format('m/d/Y') }}";
                }
                @if ($info['rows']['current']['cells']['next']['show'])
                    else if (e.keyCode == "39") {
                        document.location.href="/?date={{ $info['rows']['current']['cells']['next']['date']->format('m/d/Y') }}";
                    }
                    else if (e.keyCode == "40") {
                        document.location.href="/?date={{ $info['rows']['next']['date']->format('m/d/Y') }}";
                    }
                @endif
            }
        </script>
    </body>
</html>
