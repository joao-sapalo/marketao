<?php

namespace App\Services\Store;

use App\Models\Store;
use Illuminate\Support\Facades\Http;

class NaturalLanguageCartService
{
    public function __construct(
        private Store $store,
        private string $query
    ) {}

    public function call(): array
    {
        $productsContext = $this->buildProductsContext();
        $response = $this->callAnthropicApi($productsContext);
        return $this->parseResponse($response);
    }

    private function buildProductsContext(): string
    {
        $products = $this->store->storeProducts()->visible()->with('product')->get()->map(function ($sp) {
            $p = $sp->product;
            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'price' => $p->sale_price,
                'stock' => $p->quantity > 0 ? 'disponível' : 'esgotado',
            ];
        });

        return $products->toJson();
    }

    private function callAnthropicApi(string $productsContext): string
    {
        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            // Fallback: simple keyword matching when API is not configured
            return $this->fallbackParse($productsContext);
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1024,
                'system' => $this->systemPrompt($productsContext),
                'messages' => [
                    ['role' => 'user', 'content' => $this->query],
                ],
            ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text') ?? '';
                // Extract JSON from response
                if (preg_match('/\{.*\}/s', $content, $matches)) {
                    return $matches[0];
                }
                return $content;
            }

            return $this->fallbackParse($productsContext);
        } catch (\Exception $e) {
            return $this->fallbackParse($productsContext);
        }
    }

    private function systemPrompt(string $productsContext): string
    {
        return <<<PROMPT
És um assistente de loja angolana. O cliente vai dizer o que quer comprar
em linguagem natural (Português ou calão angolano).

Catálogo disponível (JSON):
{$productsContext}

Responde APENAS com JSON válido no formato:
{
  "items": [
    { "product_id": 1, "quantity": 2 }
  ],
  "unmatched": ["termo que não encontraste"]
}

Regras:
- Só inclui produtos disponíveis (stock: 'disponível')
- Se não encontrares correspondência, inclui em "unmatched"
- Nunca inventes produtos que não estão no catálogo
- Quantidade padrão é 1 se não especificada
PROMPT;
    }

    private function fallbackParse(string $productsContext): string
    {
        $products = json_decode($productsContext, true) ?? [];
        $query = mb_strtolower($this->query);
        $items = [];
        $unmatched = [];

        $words = preg_split('/[\s,]+/', $query);
        $numbers = [];

        // Extract quantities (numbers before product names in Portuguese)
        foreach ($words as $i => $word) {
            if (is_numeric($word)) {
                $numbers[$i] = (int)$word;
            }
        }

        // Simple keyword matching
        foreach ($products as $product) {
            $name = mb_strtolower($product['name']);
            if ($product['stock'] === 'disponível' && str_contains($query, $name)) {
                $qty = 1;
                // Check if there's a number before the product name
                $pos = mb_strpos($query, $name);
                $before = mb_substr($query, 0, $pos);
                $beforeWords = preg_split('/[\s]+/', trim($before));
                $lastWord = end($beforeWords);
                if (is_numeric($lastWord)) {
                    $qty = (int)$lastWord;
                }
                $items[] = [
                    'product_id' => $product['id'],
                    'quantity' => max(1, $qty),
                ];
            }
        }

        // If no items found, add query to unmatched
        if (empty($items)) {
            $unmatched = [$this->query];
        }

        return json_encode(['items' => $items, 'unmatched' => $unmatched]);
    }

    private function parseResponse(string $response): array
    {
        try {
            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            $items = $this->resolveItems($data['items'] ?? []);
            $unmatched = $data['unmatched'] ?? [];
            return compact('items', 'unmatched');
        } catch (\JsonException $e) {
            return ['items' => [], 'unmatched' => [$this->query]];
        }
    }

    private function resolveItems(array $rawItems): array
    {
        $result = [];
        foreach ($rawItems as $item) {
            $product = $this->store->products()->find($item['product_id']);
            if (!$product) continue;

            $result[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->sale_price,
                'quantity' => max(1, (int)($item['quantity'] ?? 1)),
            ];
        }
        return $result;
    }
}
