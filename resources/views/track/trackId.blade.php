<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track</title>
    <link rel="stylesheet" href="{{ public_path('css/style.css') }}">
</head>

<body>
    <article class="track-card">
        <header class="track-card__header">
            <img src="{{ public_path('images/parker.png') }}" alt="Parker" class="track-card__logo">
        </header>

        <main class="track-card__body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><strong>RFC EMISOR</strong></th>
                        <th><strong>RFC RECEPTOR</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $rfcEmisor }}</td>
                        <td>{{ $rfcReceptor }}</td>
                    </tr>
                </tbody>
            </table>

            <hr class="divider">

            <table class="data-table data-table--half">
                <thead>
                    <tr>
                        <th><strong>UUID</strong></th>
                        <th><strong>DIVISIÓN</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $uuid }}</td>
                        <td>{{ $division }}</td>
                    </tr>
                </tbody>
            </table>
        </main>

        <footer class="track-card__footer" style="text-align: center;">
            <span class="barcode__label">
                {{ $trackId }}
            </span>
            <img 
                src="data:image/svg+xml;base64,{{ trim($barcode) }}" 
                class="barcode__image" 
                alt="Código de barras {{ $trackId }}"
            >
        </footer>
    </article>
</body>

</html>
