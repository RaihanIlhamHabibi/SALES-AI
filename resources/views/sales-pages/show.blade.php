@php
    $g = is_array($page->generation) ? $page->generation : [];
    $savedStyle = (string) ($g['template_style'] ?? 'classic');
    $templateStyle = request()->query('style', $savedStyle);
    if (! in_array($templateStyle, ['classic', 'bold', 'minimal'], true)) {
        $templateStyle = $savedStyle;
    }
    $isBold = $templateStyle === 'bold';
    $isMinimal = $templateStyle === 'minimal';

    $headline = (string) ($g['headline'] ?? $g['title'] ?? $page->product_name);
    $subheadline = (string) ($g['subheadline'] ?? $g['subtitle'] ?? '');
    $productDescription = (string) ($g['product_description'] ?? $g['description'] ?? '');
    $benefits = is_array($g['benefits'] ?? null) ? $g['benefits'] : [];
    $features = $g['features_breakdown'] ?? $g['features'] ?? [];
    $socialProof = (string) ($g['social_proof_placeholder'] ?? 'Testimoni pelanggan akan ditampilkan di sini.');
    $pricing = (string) ($g['pricing_display'] ?? $g['pricing'] ?? ($page->price ?? ''));
    $cta = (string) ($g['call_to_action'] ?? $g['cta'] ?? 'Mulai Sekarang');
@endphp

<x-app-layout :title="$page->product_name">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl font-semibold">Preview Sales Page</h1>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sales-pages.show', ['salesPage' => $page, 'style' => 'classic']) }}" class="inline-flex items-center rounded-md border px-3 py-2 text-sm font-medium hover:bg-gray-50">Classic</a>
            <a href="{{ route('sales-pages.show', ['salesPage' => $page, 'style' => 'bold']) }}" class="inline-flex items-center rounded-md border px-3 py-2 text-sm font-medium hover:bg-gray-50">Bold</a>
            <a href="{{ route('sales-pages.show', ['salesPage' => $page, 'style' => 'minimal']) }}" class="inline-flex items-center rounded-md border px-3 py-2 text-sm font-medium hover:bg-gray-50">Minimal</a>
            <a href="{{ route('sales-pages.export', ['salesPage' => $page, 'style' => $templateStyle]) }}" class="inline-flex items-center rounded-md border px-3 py-2 text-sm font-medium hover:bg-gray-50">
                Export HTML
            </a>
            <form method="POST" action="{{ route('sales-pages.regenerate', $page) }}">
                @csrf
                <button class="inline-flex items-center rounded-md bg-black px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Regenerate Semua
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border bg-white">
        <div class="{{ $isBold ? 'bg-gradient-to-br from-purple-700 to-pink-600' : ($isMinimal ? 'bg-gradient-to-br from-slate-100 to-white text-slate-900' : 'bg-gradient-to-br from-gray-900 to-gray-700 text-white') }} px-6 py-10 {{ $isMinimal ? '' : 'text-white' }}">
            <div class="mx-auto max-w-3xl">
                <p class="text-xs uppercase tracking-wider {{ $isMinimal ? 'text-slate-500' : 'text-white/70' }}">{{ $page->target_audience ?? 'Untuk siapa saja yang butuh solusi ini' }}</p>
                <h2 class="mt-2 text-3xl font-semibold leading-tight">{{ $headline }}</h2>
                @if ($subheadline !== '')
                    <p class="mt-3 {{ $isMinimal ? 'text-slate-600' : 'text-white/80' }}">{{ $subheadline }}</p>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a href="#cta" class="inline-flex items-center rounded-md {{ $isBold ? 'bg-black text-white hover:bg-gray-900' : 'bg-white text-gray-900 hover:bg-gray-100' }} px-5 py-2.5 text-sm font-semibold">
                        {{ $cta }}
                    </a>
                    @if ($pricing !== '')
                        <span class="text-sm {{ $isMinimal ? 'text-slate-600' : 'text-white/80' }}">Mulai dari <span class="font-semibold {{ $isMinimal ? 'text-slate-900' : 'text-white' }}">{{ $pricing }}</span></span>
                    @endif
                </div>
            </div>
        </div>

        <div class="mx-auto grid max-w-5xl gap-8 px-6 py-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h3 class="text-lg font-semibold">Kenapa ini cocok untuk kamu</h3>
                <p class="mt-2 text-gray-700">{{ $productDescription !== '' ? $productDescription : $page->description }}</p>

                <div class="mt-8">
                    <h4 class="text-base font-semibold">Benefit</h4>
                    <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach ($benefits as $b)
                            <li class="rounded-lg border bg-gray-50 px-4 py-3 text-sm text-gray-800">
                                {{ is_string($b) ? $b : json_encode($b, JSON_UNESCAPED_UNICODE) }}
                            </li>
                        @endforeach
                        @if (count($benefits) === 0)
                            <li class="rounded-lg border bg-gray-50 px-4 py-3 text-sm text-gray-800">Hemat waktu dengan alur yang jelas.</li>
                            <li class="rounded-lg border bg-gray-50 px-4 py-3 text-sm text-gray-800">Lebih percaya diri karena ada panduan.</li>
                        @endif
                    </ul>
                </div>

                <div class="mt-8">
                    <h4 class="text-base font-semibold">Fitur</h4>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ((array) $features as $f)
                            @php
                                $title = is_array($f) ? (string) ($f['title'] ?? '') : (string) $f;
                                $detail = is_array($f) ? (string) ($f['detail'] ?? '') : '';
                            @endphp
                            <div class="rounded-lg border px-4 py-3">
                                <div class="text-sm font-medium">{{ $title !== '' ? $title : 'Fitur unggulan' }}</div>
                                @if ($detail !== '')
                                    <div class="mt-1 text-sm text-gray-600">{{ $detail }}</div>
                                @endif
                            </div>
                        @endforeach
                        @if (empty($features))
                            @foreach ((array) ($page->features ?? []) as $f)
                                <div class="rounded-lg border px-4 py-3">
                                    <div class="text-sm font-medium">{{ $f }}</div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="mt-10 rounded-xl border bg-gray-50 p-6">
                    <h4 class="text-base font-semibold">Social proof</h4>
                    <p class="mt-2 text-sm text-gray-700">{{ $socialProof }}</p>
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="sticky top-6 rounded-xl border p-6">
                    <p class="text-xs uppercase tracking-wider text-gray-500">Paket & Harga</p>
                    <div class="mt-2 text-2xl font-semibold">{{ $pricing !== '' ? $pricing : 'Hubungi untuk harga' }}</div>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ $page->unique_selling_points ? \Illuminate\Support\Str::limit($page->unique_selling_points, 140) : 'Masukkan USP agar CTA lebih kuat.' }}
                    </p>

                    <div id="cta" class="mt-5">
                        <a href="#" class="flex w-full items-center justify-center rounded-md bg-black px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800">
                            {{ $cta }}
                        </a>
                        <p class="mt-2 text-center text-xs text-gray-500">CTA ini placeholder. Saat deploy, bisa diarahkan ke WhatsApp / checkout.</p>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border p-4">
                    <p class="text-sm font-semibold">Regenerate per section</p>
                    <form class="mt-3 space-y-2" method="POST" action="{{ route('sales-pages.regenerate-section', $page) }}">
                        @csrf
                        <select name="section" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-300 focus:ring focus:outline-none">
                            <option value="headline">Headline</option>
                            <option value="subheadline">Subheadline</option>
                            <option value="product_description">Product Description</option>
                            <option value="benefits">Benefits</option>
                            <option value="features_breakdown">Features Breakdown</option>
                            <option value="social_proof_placeholder">Social Proof</option>
                            <option value="pricing_display">Pricing</option>
                            <option value="call_to_action">Call To Action (CTA)</option>
                        </select>
                        <button class="w-full rounded-md border px-3 py-2 text-sm font-medium hover:bg-gray-50">
                            Regenerate Section
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>

