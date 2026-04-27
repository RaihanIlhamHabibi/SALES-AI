<?php

namespace App\Http\Controllers;

use App\Models\SalesPage;
use App\Services\SalesPageAi;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesPageController extends Controller
{
    private const REGENERATABLE_SECTIONS = [
        'headline',
        'subheadline',
        'product_description',
        'benefits',
        'features_breakdown',
        'social_proof_placeholder',
        'pricing_display',
        'call_to_action',
    ];

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $pages = SalesPage::query()
            ->where('user_id', Auth::id())
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query
                        ->where('product_name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('sales-pages.index', [
            'pages' => $pages,
            'q' => $q,
        ]);
    }

    public function create()
    {
        return view('sales-pages.create');
    }

    public function store(Request $request, SalesPageAi $ai)
    {
        $data = $this->validated($request);

        $generation = $ai->generate($data);

        /** @var array<string,mixed> $content */
        $content = $generation['content'];

        $page = SalesPage::create([
            'user_id' => Auth::id(),
            'product_name' => $data['product_name'],
            'description' => $data['description'],
            'features' => $data['features'] ?? [],
            'target_audience' => $data['target_audience'] ?? null,
            'price' => $data['price'] ?? null,
            'unique_selling_points' => $data['unique_selling_points'] ?? null,
            'generation' => $content,
            'llm_provider' => $generation['provider'],
            'llm_model' => $generation['model'],
        ]);

        return redirect()
            ->route('sales-pages.show', $page)
            ->with('status', 'Sales page berhasil dibuat.');
    }

    public function show(SalesPage $salesPage)
    {
        $this->authorizeOwner($salesPage);

        return view('sales-pages.show', [
            'page' => $salesPage,
        ]);
    }

    public function regenerate(Request $request, SalesPage $salesPage, SalesPageAi $ai)
    {
        $this->authorizeOwner($salesPage);

        $input = [
            'product_name' => $salesPage->product_name,
            'description' => $salesPage->description,
            'features' => $salesPage->features ?? [],
            'target_audience' => $salesPage->target_audience,
            'price' => $salesPage->price,
            'unique_selling_points' => $salesPage->unique_selling_points,
        ];

        $generation = $ai->generate($input);
        $salesPage->generation = $generation['content'];
        $salesPage->llm_provider = $generation['provider'];
        $salesPage->llm_model = $generation['model'];
        $salesPage->save();

        return redirect()
            ->route('sales-pages.show', $salesPage)
            ->with('status', 'Sales page berhasil di-regenerate.');
    }

    public function regenerateSection(Request $request, SalesPage $salesPage, SalesPageAi $ai)
    {
        $this->authorizeOwner($salesPage);

        $validated = $request->validate([
            'section' => ['required', 'string', 'in:'.implode(',', self::REGENERATABLE_SECTIONS)],
        ]);

        $input = [
            'product_name' => $salesPage->product_name,
            'description' => $salesPage->description,
            'features' => $salesPage->features ?? [],
            'target_audience' => $salesPage->target_audience,
            'price' => $salesPage->price,
            'unique_selling_points' => $salesPage->unique_selling_points,
            'template_style' => (string) data_get($salesPage->generation, 'template_style', 'classic'),
        ];

        $section = (string) $validated['section'];
        $existing = is_array($salesPage->generation) ? $salesPage->generation : [];
        $updated = $ai->regenerateSection($input, $existing, $section);

        $salesPage->generation = $updated['content'];
        $salesPage->llm_provider = $updated['provider'];
        $salesPage->llm_model = $updated['model'];
        $salesPage->save();

        return redirect()
            ->route('sales-pages.show', $salesPage)
            ->with('status', "Section {$section} berhasil di-regenerate.");
    }

    public function export(SalesPage $salesPage): StreamedResponse
    {
        $this->authorizeOwner($salesPage);
        $style = request()->query('style');
        if (! in_array($style, ['classic', 'bold', 'minimal'], true)) {
            $style = (string) data_get($salesPage->generation, 'template_style', 'classic');
        }

        $html = view('sales-pages.export', [
            'page' => $salesPage,
            'style' => $style,
        ])->render();

        $filename = Str::slug($salesPage->product_name).'-sales-page.html';

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, $filename, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function destroy(SalesPage $salesPage)
    {
        $this->authorizeOwner($salesPage);
        $salesPage->delete();

        return redirect()
            ->route('sales-pages.index')
            ->with('status', 'Sales page dihapus.');
    }

    private function authorizeOwner(SalesPage $salesPage): void
    {
        abort_unless($salesPage->user_id === Auth::id(), 403);
    }

    /**
     * @return array{product_name:string,description:string,features?:array<int,string>|null,target_audience?:string|null,price?:string|null,unique_selling_points?:string|null,template_style?:string}
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:4000'],
            'features' => ['nullable', 'string', 'max:2000'],
            'target_audience' => ['nullable', 'string', 'max:160'],
            'price' => ['nullable', 'string', 'max:80'],
            'unique_selling_points' => ['nullable', 'string', 'max:2000'],
            'template_style' => ['nullable', 'string', 'in:classic,bold,minimal'],
        ]);

        $features = collect(explode(',', (string) ($validated['features'] ?? '')))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values()
            ->all();

        return [
            'product_name' => (string) $validated['product_name'],
            'description' => (string) $validated['description'],
            'features' => $features,
            'target_audience' => Arr::get($validated, 'target_audience') ? (string) $validated['target_audience'] : null,
            'price' => Arr::get($validated, 'price') ? (string) $validated['price'] : null,
            'unique_selling_points' => Arr::get($validated, 'unique_selling_points') ? (string) $validated['unique_selling_points'] : null,
            'template_style' => Arr::get($validated, 'template_style') ? (string) $validated['template_style'] : 'classic',
        ];
    }
}

