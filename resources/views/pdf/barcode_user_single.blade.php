<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            size: 250pt 350pt;
            margin: 10px;
        }

        body {
            font-family: sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            border: 2px solid #333;
            padding: 10px;
        }

        h4 {
            margin: 5px 0;
            text-transform: uppercase;
        }

        img {
            width: 150px;
            height: 150px;
        }

        .id-text {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <h4>{{ $user->name }}</h4>

    <div>
        <img src="data:image/png;base64,{{ $barcode }}" alt="QR Code">
    </div>

    <div class="id-text">ID: {{ $user->no_ID }}</div>
</body>

</html>
