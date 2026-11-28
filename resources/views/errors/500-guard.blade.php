<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Kegagalan Guard Konfigurasi Produksi | Kasirku</title>
</head>
<body>
    <main class="guard-error">
        <h1>Konfigurasi Produksi Belum Memenuhi Guard Rules</h1>
        <p>Aplikasi menghentikan boot pada environment produksi karena ditemukan konfigurasi dev/unsafe. Nilai rahasia tidak pernah ditampilkan.</p>

        @if (!empty($violations))
            <table>
                <thead>
                    <tr>
                        <th>Rule</th>
                        <th>Environment Key</th>
                        <th>Nilai Saat Ini</th>
                        <th>Harapan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($violations as $item)
                        <tr>
                            <td>#{{ $item['rule'] }}</td>
                            <td><code>{{ $item['env_key'] }}</code></td>
                            <td><code>{{ $item['actual'] }}</code></td>
                            <td>{{ $item['expected'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>
</body>
</html>
