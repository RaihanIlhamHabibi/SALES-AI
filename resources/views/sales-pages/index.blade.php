<x-app-layout title="History Sales Page">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold">History</h1>
            <p class="mt-1 text-sm text-gray-600">Semua sales page yang pernah kamu generate.</p>
        </div>

        <form class="flex gap-2" method="GET" action="{{ route('sales-pages.index') }}">
            <input name="q" value="{{ $q }}" class="w-64 max-w-full rounded-md border-gray-300 focus:border-blue-300 focus:ring focus:outline-none" placeholder="Cari nama/desc..." />
            <button class="rounded-md border bg-white px-3 py-2 text-sm hover:bg-gray-50">Cari</button>
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border bg-white">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Produk</th>
                    <th class="px-4 py-3 text-left font-medium hidden md:table-cell">Ringkas</th>
                    <th class="px-4 py-3 text-left font-medium">Dibuat</th>
                    <th class="px-4 py-3 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($pages as $page)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $page->product_name }}</div>
                            <div class="text-xs text-gray-500">{{ $page->price ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-700">
                            {{ \Illuminate\Support\Str::limit($page->description, 90) }}
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $page->created_at?->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('sales-pages.show', $page) }}" class="rounded-md bg-black px-3 py-2 text-xs font-medium text-white hover:bg-gray-800">Preview</a>
                                <form method="POST" action="{{ route('sales-pages.destroy', $page) }}" onsubmit="return confirm('Hapus sales page ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-md border px-3 py-2 text-xs font-medium hover:bg-gray-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-8 text-center text-gray-600" colspan="4">
                            Belum ada data. <a class="underline" href="{{ route('sales-pages.create') }}">Generate sales page pertama</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pages->links() }}
    </div>
</x-app-layout>

