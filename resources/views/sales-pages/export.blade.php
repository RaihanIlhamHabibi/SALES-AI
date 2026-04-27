@php
    $g = is_array($page->generation) ? $page->generation : [];
    $style = isset($style) && is_string($style) ? $style : (string) ($g['template_style'] ?? 'classic');
    $isBold = $style === 'bold';
    $isMinimal = $style === 'minimal';
    $headline = (string) ($g['headline'] ?? $page->product_name);
    $subheadline = (string) ($g['subheadline'] ?? '');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $page->product_name }}</title>
    <style>
        body{font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial; margin:0; color:#0f172a; background:{{ $isMinimal ? '#ffffff' : '#f8fafc' }}}
        .wrap{max-width:1000px;margin:0 auto;padding:24px}
        .hero{background:{{ $isBold ? 'linear-gradient(135deg,#7e22ce,#ec4899)' : ($isMinimal ? 'linear-gradient(135deg,#f1f5f9,#ffffff)' : 'linear-gradient(135deg,#0f172a,#334155)') }};color:{{ $isMinimal ? '#0f172a' : '#fff' }};border-radius:16px;padding:40px}
        .hero h1{margin:0;font-size:40px;line-height:1.1}
        .hero p{margin:12px 0 0;opacity:.85}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px}
        .grid{display:grid;gap:16px}
        @media(min-width:900px){.grid{grid-template-columns:2fr 1fr}}
        .btn{display:inline-block;background:{{ $isBold ? '#111827' : '#fff' }};color:{{ $isBold ? '#fff' : '#0f172a' }};font-weight:700;padding:12px 16px;border-radius:10px;text-decoration:none;margin-top:18px}
        .muted{color:#64748b;font-size:14px}
        ul{padding-left:18px}
    </style>
</head>
<body>
    <div class="wrap">
        <section class="hero">
            <div class="muted" style="color:{{ $isMinimal ? '#64748b' : '#cbd5e1' }}">{{ $page->target_audience ?? 'Landing page' }}</div>
            <h1>{{ $headline }}</h1>
            @if ($subheadline !== '')
                <p>{{ $subheadline }}</p>
            @endif
            <a class="btn" href="#cta">{{ (string) ($g['call_to_action'] ?? 'Mulai Sekarang') }}</a>
        </section>

        <div style="height:18px"></div>

        <div class="grid">
            <section class="card">
                <h2 style="margin-top:0">Deskripsi</h2>
                <p>{{ (string) ($g['product_description'] ?? $page->description) }}</p>

                <h3>Benefit</h3>
                <ul>
                    @foreach ((array) ($g['benefits'] ?? []) as $b)
                        <li>{{ is_string($b) ? $b : json_encode($b, JSON_UNESCAPED_UNICODE) }}</li>
                    @endforeach
                </ul>

                <h3>Fitur</h3>
                <ul>
                    @foreach ((array) ($g['features_breakdown'] ?? $page->features ?? []) as $f)
                        @php $t = is_array($f) ? ($f['title'] ?? '') : $f; @endphp
                        <li>{{ is_string($t) ? $t : json_encode($t, JSON_UNESCAPED_UNICODE) }}</li>
                    @endforeach
                </ul>

                <h3>Social Proof</h3>
                <p class="muted">{{ (string) ($g['social_proof_placeholder'] ?? 'Testimoni pelanggan akan ditampilkan di sini.') }}</p>
            </section>

            <aside class="card" id="cta">
                <div class="muted">Harga</div>
                <div style="font-size:28px;font-weight:800;margin-top:6px">{{ (string) ($g['pricing_display'] ?? $page->price ?? 'Hubungi untuk harga') }}</div>
                <p class="muted" style="margin-top:10px">{{ $page->unique_selling_points ?? '' }}</p>
                <a class="btn" style="background:#0f172a;color:#fff" href="#">{{ (string) ($g['call_to_action'] ?? 'Mulai Sekarang') }}</a>
                <p class="muted" style="margin-top:12px">Export HTML ini adalah template statis dari hasil generate.</p>
            </aside>
        </div>
    </div>
</body>
</html>

