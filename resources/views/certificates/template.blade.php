<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
        }
        .page {
            position: relative;
            width: {{ $template->page_width_mm }}mm;
            height: {{ $template->page_height_mm }}mm;
        }
        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: {{ $template->page_width_mm }}mm;
            height: {{ $template->page_height_mm }}mm;
        }
        .field {
            position: absolute;
            font-family: 'Helvetica', sans-serif;
        }
    </style>
</head>
<body>
    <div class="page">
        <img src="{{ $backgroundPath }}" class="background">

        @foreach(['name', 'module', 'date', 'score'] as $key)
            @php $config = $template->getFieldConfig($key); @endphp
            @if($config['enabled'] && !empty($values[$key]))
                <div class="field" style="
                    top: {{ $config['y'] }}mm;
                    left: 0;
                    width: {{ $template->page_width_mm }}mm;
                    text-align: {{ $config['align'] }};
                    font-size: {{ $config['font_size'] }}pt;
                    color: {{ $config['color'] }};
                ">
                    @if($config['align'] !== 'center')
                        {{-- Kalau bukan center, geser pakai margin sesuai X --}}
                        <span style="margin-left: {{ $config['align'] === 'left' ? $config['x'] : 0 }}mm; margin-right: {{ $config['align'] === 'right' ? ($template->page_width_mm - $config['x']) : 0 }}mm;">
                            {{ $values[$key] }}
                        </span>
                    @else
                        {{ $values[$key] }}
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</body>
</html>
