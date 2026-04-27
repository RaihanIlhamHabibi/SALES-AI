<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SalesPageAi
{
    /**
     * @param  array{product_name:string,description:string,features?:array<int,string>|null,target_audience?:string|null,price?:string|null,unique_selling_points?:string|null,template_style?:string|null}  $input
     * @return array{provider:string,model:?string,content:array<string,mixed>}
     */
    public function generate(array $input): array
    {
        $provider = config('services.sales_ai.provider', 'openai');

        return match ($provider) {
            'openai' => $this->generateWithOpenAi($input),
            'openrouter' => $this->generateWithOpenRouter($input),
            'gemini' => $this->generateWithGemini($input),
            default => throw new RuntimeException("Unsupported sales AI provider: {$provider}"),
        };
    }

    /**
     * @param  array{product_name:string,description:string,features?:array<int,string>|null,target_audience?:string|null,price?:string|null,unique_selling_points?:string|null,template_style?:string|null}  $input
     * @param  array<string,mixed>  $currentContent
     * @return array{provider:string,model:?string,content:array<string,mixed>}
     */
    public function regenerateSection(array $input, array $currentContent, string $section): array
    {
        $fresh = $this->generate($input);
        $candidate = $fresh['content'][$section] ?? null;

        // Keep backward compatibility with older key names.
        if ($candidate === null && $section === 'call_to_action') {
            $candidate = $fresh['content']['cta'] ?? null;
        }

        if ($candidate === null) {
            throw new RuntimeException("AI response did not include section: {$section}.");
        }

        $updated = $currentContent;
        $updated[$section] = $candidate;
        $updated['template_style'] = $input['template_style'] ?? ($currentContent['template_style'] ?? 'classic');

        return [
            'provider' => $fresh['provider'],
            'model' => $fresh['model'],
            'content' => $updated,
        ];
    }

    /**
     * Generates a structured JSON payload.
     *
     * Expected keys:
     * - headline, subheadline
     * - description
     * - benefits (array of strings)
     * - features (array of {title, detail?} or strings)
     * - social_proof_placeholder
     * - pricing (string)
     * - cta (string)
     * - faq (optional array)
     */
    private function generateWithOpenAi(array $input): array
    {
        $apiKey = config('services.sales_ai.openai.api_key');
        $model = config('services.sales_ai.openai.model', 'gpt-4.1-mini');
        $verifySsl = (bool) config('services.sales_ai.verify_ssl', true);

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Missing OPENAI_API_KEY. Set it in .env to enable AI generation.');
        }

        $features = array_values(array_filter(array_map(
            static fn ($v) => is_string($v) ? trim($v) : '',
            $input['features'] ?? []
        )));

        $system = implode("\n", [
            'You generate a structured sales page JSON for a landing page.',
            'Return ONLY valid JSON. No markdown, no backticks, no extra text.',
            'Use Indonesian language unless the product name strongly implies otherwise.',
            'Avoid making factual claims that are not provided; use persuasive but safe copy.',
        ]);

        $user = [
            'product_name' => $input['product_name'],
            'description' => $input['description'],
            'features' => $features,
            'target_audience' => $input['target_audience'] ?? null,
            'price' => $input['price'] ?? null,
            'unique_selling_points' => $input['unique_selling_points'] ?? null,
            'template_style' => $input['template_style'] ?? 'classic',
            'required_sections' => [
                'headline',
                'subheadline',
                'product_description',
                'benefits',
                'features_breakdown',
                'social_proof_placeholder',
                'pricing_display',
                'call_to_action',
            ],
        ];

        $client = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(60);

        if (! $verifySsl) {
            $client = $client->withoutVerifying();
        }

        $response = $client
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => json_encode($user, JSON_UNESCAPED_UNICODE)],
                ],
            ]);

        if (! $response->ok()) {
            $msg = $response->json('error.message') ?? $response->body();
            throw new RuntimeException('AI request failed: '.Str::limit((string) $msg, 240));
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content) || $content === '') {
            throw new RuntimeException('AI response was empty.');
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('AI response was not valid JSON.');
        }

        return [
            'provider' => 'openai',
            'model' => $model,
            'content' => $decoded + ['template_style' => ($input['template_style'] ?? 'classic')],
        ];
    }

    private function generateWithOpenRouter(array $input): array
    {
        $apiKey = config('services.sales_ai.openrouter.api_key');
        $primaryModel = config('services.sales_ai.openrouter.model', 'openrouter/auto');
        $fallbackModels = config('services.sales_ai.openrouter.fallback_models', 'google/gemma-2-9b-it:free,meta-llama/llama-3.1-8b-instruct:free');
        $baseUrl = rtrim((string) config('services.sales_ai.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
        $verifySsl = (bool) config('services.sales_ai.verify_ssl', true);

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Missing OPENROUTER_API_KEY. Set it in .env to enable AI generation.');
        }

        $features = array_values(array_filter(array_map(
            static fn ($v) => is_string($v) ? trim($v) : '',
            $input['features'] ?? []
        )));

        $system = implode("\n", [
            'You generate a structured sales page JSON for a landing page.',
            'Return ONLY valid JSON. No markdown, no backticks, no extra text.',
            'Use Indonesian language unless the product name strongly implies otherwise.',
            'Avoid making factual claims that are not provided; use persuasive but safe copy.',
        ]);

        $user = [
            'product_name' => $input['product_name'],
            'description' => $input['description'],
            'features' => $features,
            'target_audience' => $input['target_audience'] ?? null,
            'price' => $input['price'] ?? null,
            'unique_selling_points' => $input['unique_selling_points'] ?? null,
            'template_style' => $input['template_style'] ?? 'classic',
            'required_sections' => [
                'headline',
                'subheadline',
                'product_description',
                'benefits',
                'features_breakdown',
                'social_proof_placeholder',
                'pricing_display',
                'call_to_action',
            ],
        ];

        $models = collect([
            is_string($primaryModel) ? trim($primaryModel) : '',
            ...array_map('trim', explode(',', is_string($fallbackModels) ? $fallbackModels : '')),
        ])->filter()->unique()->values()->all();

        if ($models === []) {
            $models = ['openrouter/auto'];
        }

        $client = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(60);

        if (! $verifySsl) {
            $client = $client->withoutVerifying();
        }

        $lastError = null;
        foreach ($models as $model) {
            $response = $client->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => json_encode($user, JSON_UNESCAPED_UNICODE)],
                ],
            ]);

            if (! $response->ok()) {
                $msg = (string) ($response->json('error.message') ?? $response->body());
                $lastError = "model {$model}: ".$msg;
                $normalized = strtolower($msg);

                if ($response->status() === 404 || $response->status() === 429 || Str::contains($normalized, ['no endpoints found', 'not found', 'rate limit'])) {
                    continue;
                }

                throw new RuntimeException('AI request failed: '.Str::limit($lastError, 240));
            }

            $content = $response->json('choices.0.message.content');
            if (! is_string($content) || trim($content) === '') {
                throw new RuntimeException("AI response was empty for model {$model}.");
            }

            $decoded = json_decode($content, true);
            if (! is_array($decoded)) {
                throw new RuntimeException("AI response was not valid JSON for model {$model}.");
            }

            return [
                'provider' => 'openrouter',
                'model' => $model,
                'content' => $decoded + ['template_style' => ($input['template_style'] ?? 'classic')],
            ];
        }

        throw new RuntimeException('AI request failed: '.Str::limit((string) $lastError, 240));
    }

    /**
     * Generates a structured JSON payload with Gemini.
     */
    private function generateWithGemini(array $input): array
    {
        $apiKey = config('services.sales_ai.gemini.api_key');
        $primaryModel = config('services.sales_ai.gemini.model', 'gemini-2.0-flash');
        $fallbackModels = config('services.sales_ai.gemini.fallback_models', 'gemini-1.5-flash');
        $verifySsl = (bool) config('services.sales_ai.verify_ssl', true);

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Missing GEMINI_API_KEY. Set it in .env to enable AI generation.');
        }

        $features = array_values(array_filter(array_map(
            static fn ($v) => is_string($v) ? trim($v) : '',
            $input['features'] ?? []
        )));

        $system = implode("\n", [
            'You generate a structured sales page JSON for a landing page.',
            'Return ONLY valid JSON. No markdown, no backticks, no extra text.',
            'Use Indonesian language unless the product name strongly implies otherwise.',
            'Avoid making factual claims that are not provided; use persuasive but safe copy.',
        ]);

        $user = [
            'product_name' => $input['product_name'],
            'description' => $input['description'],
            'features' => $features,
            'target_audience' => $input['target_audience'] ?? null,
            'price' => $input['price'] ?? null,
            'unique_selling_points' => $input['unique_selling_points'] ?? null,
            'template_style' => $input['template_style'] ?? 'classic',
            'required_sections' => [
                'headline',
                'subheadline',
                'product_description',
                'benefits',
                'features_breakdown',
                'social_proof_placeholder',
                'pricing_display',
                'call_to_action',
            ],
        ];

        $models = collect([
            is_string($primaryModel) ? trim($primaryModel) : '',
            ...array_map('trim', explode(',', is_string($fallbackModels) ? $fallbackModels : '')),
        ])->filter()->unique()->values()->all();

        if ($models === []) {
            $models = ['gemini-1.5-flash'];
        }

        $client = Http::acceptJson()->withQueryParameters(['key' => $apiKey])->timeout(60);

        if (! $verifySsl) {
            $client = $client->withoutVerifying();
        }

        $lastError = null;
        $quotaError = null;
        foreach ($models as $model) {
            $response = $client->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [[
                    'parts' => [[
                        'text' => $system."\n\nInput:\n".json_encode($user, JSON_UNESCAPED_UNICODE),
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if (! $response->ok()) {
                $msg = (string) ($response->json('error.message') ?? $response->body());
                $lastError = "model {$model}: ".$msg;
                $normalized = strtolower($msg);
                if (Str::contains($normalized, ['quota', 'rate limit', 'exceeded'])) {
                    $quotaError = $lastError;
                }

                if ($response->status() === 429 || $response->status() === 404 || Str::contains($normalized, ['quota', 'rate limit', 'not found', 'exceeded'])) {
                    continue;
                }

                throw new RuntimeException('AI request failed: '.Str::limit($lastError, 240));
            }

            $content = $response->json('candidates.0.content.parts.0.text');
            if (! is_string($content) || trim($content) === '') {
                throw new RuntimeException("AI response was empty for model {$model}.");
            }

            $json = trim($content);
            if (str_starts_with($json, '```')) {
                $json = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $json) ?? $json;
                $json = trim($json);
            }

            $decoded = json_decode($json, true);
            if (! is_array($decoded)) {
                throw new RuntimeException("AI response was not valid JSON for model {$model}.");
            }

            return [
                'provider' => 'gemini',
                'model' => $model,
                'content' => $decoded + ['template_style' => ($input['template_style'] ?? 'classic')],
            ];
        }

        throw new RuntimeException('AI request failed: '.Str::limit((string) ($quotaError ?? $lastError), 240));
    }
}

