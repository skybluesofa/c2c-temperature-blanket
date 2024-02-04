<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ (new Carbon\Carbon())->format('Y') }} Temperature Blanket</title>
        
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>
    <body class="antialiased bg-stone-800">
        <div class="fixed top-0 left-0 right-0 bg-stone-900 p-8 flex flex-row h-24">
            <h1 class="text-orange-800 text-center text-2xl font-bold w-1/3"><a href="/">{{ (new Carbon\Carbon())->format('Y') }} Temperature Blanket</a></h1>
            <h2 class="text-orange-300 text-center text-xl w-2/3">
                <a href="/?date={{ $info['rows']['current']['date']->clone()->subday()->format('m/d/Y') }}" class="text-orange-700 hover:text-stone-900 hover:bg-orange-700 p-1"> &lt; </a>
                <a href="/?date={{ $info['rows']['current']['date']->format('m/d/Y') }}" class="hover:text-stone-900 hover:bg-orange-300 p-1"> {{ $info['rows']['current']['date']->format('m/d/Y') }}  </a>
                <a href="/?date={{ $info['rows']['current']['date']->clone()->addday()->format('m/d/Y') }}" class="text-orange-700 hover:text-stone-900 hover:bg-orange-700 p-1"> &gt; </a>
            </h2>
        </div>

        <div class="grid grid-cols-3 gap-4 p-8 pt-32">
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
                @if (empty($positionInformation['weather']))
                    <div class="bg-stone-900 text-stone-600 p-4 flex flex-row"><div class="grow self-center text-center"><i class="bi bi-slash-circle block text-3xl pb-2"></i>No data available for this date</div></div>
                @else
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
                @endif
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
