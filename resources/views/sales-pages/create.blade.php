<x-app-layout title="Buat Sales Page">
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border bg-white p-6">
            <h1 class="text-xl font-semibold">AI Sales Page Generator</h1>
            <p class="mt-1 text-sm text-gray-600">Isi data produk/jasa, lalu generate landing page sales yang siap dipresentasikan.</p>

            <form class="mt-6 space-y-4" method="POST" action="{{ route('sales-pages.store') }}">
                @csrf

                <div>
                    <label class="text-sm font-medium">Nama Produk / Jasa</label>
                    <input name="product_name" value="{{ old('product_name') }}" class="mt-1 w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring focus:outline-none" placeholder="Contoh: Kursus Ngaji Online" required />
                    @error('product_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Deskripsi Singkat</label>
                    <textarea name="description" rows="5" class="mt-1 w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring focus:outline-none" placeholder="Ceritakan produk/jasa kamu..." required>{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Fitur Utama (pisahkan dengan koma)</label>
                    <input name="features" value="{{ old('features') }}" class="mt-1 w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring focus:outline-none" placeholder="Contoh: Live Zoom, Modul PDF, Grup WhatsApp" />
                    @error('features') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium">Target Audiens</label>
                        <input name="target_audience" value="{{ old('target_audience') }}" class="mt-1 w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring focus:outline-none" placeholder="Contoh: Pemula usia 15–30" />
                        @error('target_audience') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium">Harga</label>
                        <input name="price" value="{{ old('price') }}" class="mt-1 w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring focus:outline-none" placeholder="Contoh: Rp 199.000" />
                        @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium">Unique Selling Points (USP)</label>
                    <textarea name="unique_selling_points" rows="3" class="mt-1 w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring focus:outline-none" placeholder="Apa yang bikin kamu beda?">{{ old('unique_selling_points') }}</textarea>
                    @error('unique_selling_points') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Template Style</label>
                    <select name="template_style" class="mt-1 w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring focus:outline-none">
                        <option value="classic" @selected(old('template_style', 'classic') === 'classic')>Classic (balanced)</option>
                        <option value="bold" @selected(old('template_style') === 'bold')>Bold (high contrast)</option>
                        <option value="minimal" @selected(old('template_style') === 'minimal')>Minimal (clean/light)</option>
                    </select>
                    @error('template_style') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-md bg-black px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                    Generate Sales Page
                </button>

                <p class="text-xs text-gray-500">
                    Pastikan API key provider aktif kamu sudah terisi di <code class="rounded bg-gray-100 px-1 py-0.5">.env</code>.
                </p>
            </form>
        </div>

        <div class="rounded-lg border bg-white p-6">
            <h2 class="text-base font-semibold">Hasil Preview</h2>
            <p class="mt-1 text-sm text-gray-600">Setelah generate, kamu akan melihat preview layout seperti landing page.</p>

            <div class="mt-4 rounded-lg border bg-gray-50 p-4">
                <div class="h-4 w-3/4 rounded bg-gray-200"></div>
                <div class="mt-3 h-3 w-2/3 rounded bg-gray-200"></div>
                <div class="mt-6 space-y-2">
                    <div class="h-3 w-full rounded bg-gray-200"></div>
                    <div class="h-3 w-11/12 rounded bg-gray-200"></div>
                    <div class="h-3 w-10/12 rounded bg-gray-200"></div>
                </div>
                <div class="mt-6 grid gap-2 sm:grid-cols-2">
                    <div class="h-16 rounded bg-white border"></div>
                    <div class="h-16 rounded bg-white border"></div>
                </div>
                <div class="mt-6 h-10 w-40 rounded bg-black/80"></div>
            </div>
        </div>
    </div>
</x-app-layout>

