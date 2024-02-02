<html>
    <body style="background:#ccc;">
        <h1 style="text-align:center;color:#fff">2024 Temperature Blanket</h1>
        <div style="color:#fff;position:absolute;top:10px;right:10px;">Data from {{ $info['meta']['cachedDate'] }}</div>
        <h2 style="text-align:center;color:#fff">
            <a href="/?date='.$prevDate->format('m/d/Y').'" style="text-decoration:none;color:#fff;">&lt;</a>
            &nbsp;  {{ $info['rows']['current']['date'] }} &nbsp;
            <a href="/?date='.$nextDate->format('m/d/Y').'" style="text-decoration:none;color:#fff">&gt;</a>
        </h2>
        
        <table border=1 style="width:100%;">
            <tr>
                @include('c2c.cell.metadata', [
                    'meta' => $info['meta'],
                    'date' => $info['rows']['current']['cells']['previous']['date'],
                ])
                @include('c2c.cell.metadata', [
                    'meta' => $info['meta'],
                    'date' => $info['rows']['current']['cells']['current']['date'],
                ])
                @include('c2c.cell.metadata', [
                    'meta' => $info['meta'],
                    'date' => $info['rows']['current']['cells']['next']['date'],
                ])
            </tr>
            @foreach ($info['meta']['design'] as $rows)
                <tr>
                    @foreach ($info['rows']['current']['cells'] as $position => $positionInformation)
                        @foreach ($rows as $cellDesign)
                            @include('c2c.cell', [
                                'cellDesign' => $cellDesign,
                                'positionInformation' => $positionInformation,
                                'colorInformation' => $info['meta']['colors'],
                            ])
                        @endforeach
                    @endforeach
                </tr>
            @endforeach
        </table>

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