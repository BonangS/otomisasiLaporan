<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konversi Laporan Traceability</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Arial, Helvetica, sans-serif;
            --ink: #17202a;
            --muted: #65717f;
            --line: #cfd7df;
            --field: #f7f9fb;
            --accent: #146c5f;
            --accent-dark: #0f544a;
            --danger: #b42318;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            background: #eef2f5;
        }

        main {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 40px 0;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 380px;
            gap: 28px;
            align-items: start;
        }

        .panel, .preview {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 16px 40px rgba(25, 42, 62, 0.08);
        }

        .panel { padding: 28px; }
        .preview { overflow: hidden; }

        h1 {
            margin: 0 0 10px;
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.05;
            letter-spacing: 0;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        form { margin-top: 26px; }

        label {
            display: block;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .dropzone {
            display: grid;
            place-items: center;
            min-height: 220px;
            padding: 24px;
            border: 2px dashed #97a6b2;
            border-radius: 8px;
            background: var(--field);
            text-align: center;
        }

        input[type="file"] { width: 100%; }

        .filename {
            min-height: 24px;
            margin-top: 12px;
            color: var(--muted);
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            margin-top: 22px;
            padding: 0 22px;
            border: 0;
            border-radius: 6px;
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover { background: var(--accent-dark); }

        .error {
            margin-top: 16px;
            padding: 12px 14px;
            border: 1px solid #f3b6ae;
            border-radius: 6px;
            background: #fff1ef;
            color: var(--danger);
        }

        .sheet-head {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            background: #dfe7ec;
            border-bottom: 1px solid var(--line);
            font-size: 12px;
            font-weight: 700;
        }

        .sheet-head span, .sheet-row span {
            min-height: 38px;
            padding: 10px;
            border-right: 1px solid var(--line);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sheet-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border-bottom: 1px solid var(--line);
            font-size: 13px;
        }

        .sheet-row:nth-child(even) { background: #fafafa; }

        .preview-footer {
            padding: 16px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        @media (max-width: 860px) {
            main { padding: 24px 0; }
            .layout { grid-template-columns: 1fr; }
            .panel { padding: 22px; }
        }
    </style>
</head>
<body>
    <main>
        <div class="layout">
            <section class="panel" aria-labelledby="page-title">
                <h1 id="page-title">Konversi foto laporan ke Excel</h1>
                <p>Upload foto laporan traceability stuffing/filling. Sistem akan membaca tabel dari gambar lalu membuat file Excel memakai template laporan yang tersedia.</p>

                <form action="{{ route('reports.convert') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <label for="report_image">Gambar laporan</label>
                    <div class="dropzone">
                        <input id="report_image" name="report_image" type="file" accept="image/*" required>
                    </div>
                    <div class="filename" id="filename">Belum ada file dipilih.</div>

                    @error('report_image')
                        <div class="error">{{ $message }}</div>
                    @enderror

                    <button type="submit" aria-label="Konversi gambar laporan menjadi file Excel">
                        <span aria-hidden="true">?</span>
                        Convert ke Excel
                    </button>
                </form>
            </section>

            <aside class="preview" aria-label="Preview format Excel">
                <div class="sheet-head">
                    <span>TANGGAL</span>
                    <span>NOMOR FCL</span>
                    <span>MERK</span>
                    <span>JAM</span>
                </div>
                @foreach ([['18/02', 'MEDU...', 'Tradekey 12', '04:35'], ['', '', '', '04:40'], ['', '', '', '04:45'], ['', '', '', '04:50'], ['', '', '', '05:00'], ['', '', '', '05:05']] as $row)
                    <div class="sheet-row">
                        <span>{{ $row[0] }}</span>
                        <span>{{ $row[1] }}</span>
                        <span>{{ $row[2] }}</span>
                        <span>{{ $row[3] }}</span>
                    </div>
                @endforeach
                <div class="preview-footer">Kolom lengkap yang diekspor: tanggal, nomor FCL, merk, size, qty pcs, jam filling, mesin, kode mould, kode operator, temperature, resin.</div>
            </aside>
        </div>
    </main>

    <script>
        const input = document.getElementById('report_image');
        const filename = document.getElementById('filename');

        input.addEventListener('change', () => {
            filename.textContent = input.files.length ? input.files[0].name : 'Belum ada file dipilih.';
        });
    </script>
</body>
</html>
