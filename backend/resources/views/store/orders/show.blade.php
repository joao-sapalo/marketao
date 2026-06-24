@extends('store.layouts.store')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500">Referência</p>
                    <p class="text-xl font-bold text-gray-900">{{ $order->reference }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    @switch($order->status)
                        @case(0) bg-yellow-100 text-yellow-800 @break
                        @case(1) bg-blue-100 text-blue-800 @break
                        @case(2) bg-indigo-100 text-indigo-800 @break
                        @case(3) bg-purple-100 text-purple-800 @break
                        @case(4) bg-green-100 text-green-800 @break
                        @case(5) bg-red-100 text-red-800 @break
                        @default bg-gray-100 text-gray-800
                    @endswitch
                ">
                    {{ $order->statusLabel() }}
                </span>
            </div>

            <!-- Status Timeline -->
            <div class="flex items-center gap-1 mb-6">
                @php
                    $steps = [
                        0 => ['label' => 'Recebido', 'done' => $order->status >= 0],
                        1 => ['label' => 'Confirmado', 'done' => $order->status >= 1],
                        2 => ['label' => 'Em prep.', 'done' => $order->status >= 2],
                        4 => ['label' => 'Entregue', 'done' => $order->status >= 4],
                    ];
                @endphp
                @foreach($steps as $key => $step)
                    @if(!$loop->first)
                    <div class="flex-1 h-1 rounded {{ $step['done'] ? 'bg-blue-500' : 'bg-gray-200' }}"></div>
                    @endif
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold {{ $step['done'] ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                            {{ $step['done'] ? '✓' : $loop->iteration }}
                        </div>
                        <span class="text-xs mt-1 {{ $step['done'] ? 'text-blue-600' : 'text-gray-400' }}">{{ $step['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Payment Info (if pending) -->
        @if(in_array($order->payment_method, [1, 2]) && $order->payment_status === 0)
        <div class="bg-white rounded-xl shadow-sm border border-yellow-200 p-6 mb-6">
            <h3 class="font-semibold text-lg mb-3">💳 Dados de Pagamento</h3>
            @if($order->store->bank_name)
            <div class="space-y-2 text-sm">
                <p><span class="text-gray-500">Banco:</span> {{ $order->store->bank_name }}</p>
                <p><span class="text-gray-500">Titular:</span> {{ $order->store->bank_holder }}</p>
                <p><span class="text-gray-500">IBAN:</span> {{ $order->store->bank_iban }}</p>
                <p><span class="text-gray-500">Referência:</span> <strong>{{ $order->payment_reference }}</strong></p>
            </div>
            <p class="text-xs text-gray-400 mt-2">⚠ Usa esta referência na descrição da transferência</p>
            @endif
        </div>
        @endif

        <!-- Items -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="p-4 border-b border-gray-200 font-semibold">Itens do Pedido</div>
            <div class="divide-y divide-gray-200">
                @foreach($order->items as $item)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                        <p class="text-sm text-gray-500">{{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', ' ') }} AOA</p>
                    </div>
                    <p class="font-semibold">{{ number_format($item->total, 0, ',', ' ') }} AOA</p>
                </div>
                @endforeach
            </div>
            <div class="p-4 bg-gray-50 flex items-center justify-between">
                <span class="font-semibold">Total</span>
                <span class="font-bold text-lg text-blue-600">{{ number_format($order->total, 0, ',', ' ') }} AOA</span>
            </div>
        </div>

        <!-- Contact Store -->
        @if($order->store->whatsapp)
        <div class="text-center">
            <a href="https://wa.me/244{{ preg_replace('/\D/', '', $order->store->whatsapp) }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Fala com a loja no WhatsApp
            </a>
        </div>
        @endif
    </div>

    <script>
    // Poll for order status updates every 30s
    setInterval(() => {
        fetch('/api/s/{{ $store->slug }}/orders/{{ $order->id }}')
            .then(r => r.json())
            .then(data => {
                if (data.data?.status !== {{ $order->status }}) {
                    location.reload();
                }
            })
            .catch(() => {});
    }, 30000);
    </script>
@endsection
