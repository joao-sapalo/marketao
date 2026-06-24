<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public const TEMPLATE_MAP = [
        'order_created' => 'order_received',
        'order_confirmed' => 'order_confirmed',
        'order_delivered' => 'order_delivered',
        'order_cancelled' => 'order_cancelled',
        'payment_verified' => 'payment_verified',
        'review_request' => 'review_request',
        'new_order_merchant' => 'new_order_merchant',
    ];

    public function __construct(
        private ?string $phone,
        private string $event,
        private array $variables = []
    ) {}

    public function call(): void
    {
        if (empty($this->phone)) return;

        $normalizedPhone = $this->normalizePhone($this->phone);
        $template = self::TEMPLATE_MAP[$this->event] ?? null;

        if (!$template) {
            Log::warning("WhatsApp: template não encontrado para evento {$this->event}");
            return;
        }

        $token = config('services.whatsapp.token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $apiVersion = config('services.whatsapp.api_version', 'v19.0');

        if (!$token || !$phoneNumberId) {
            Log::info("WhatsApp simulada: Para {$normalizedPhone}, template: {$template}", $this->variables);
            return;
        }

        try {
            $response = Http::withToken($token)
                ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $normalizedPhone,
                    'type' => 'template',
                    'template' => [
                        'name' => $template,
                        'language' => ['code' => 'pt_AO'],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => collect($this->variables)->map(fn($v) => ['type' => 'text', 'text' => (string)$v])->values()->toArray(),
                            ],
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                Log::error("WhatsApp API error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp exception: " . $e->getMessage());
        }
    }

    private function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/\D/', '', $phone);
        if (str_starts_with($cleaned, '244')) {
            return "+{$cleaned}";
        }
        return "+244{$cleaned}";
    }
}
