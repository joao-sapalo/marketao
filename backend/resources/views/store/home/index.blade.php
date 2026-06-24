@extends('store.layouts.store')

@section('content')
    <!-- Trust Score -->
    @if($store->trust_score > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <div class="flex items-center gap-1">
                <span class="text-yellow-400 text-lg">★</span>
                <span class="font-bold text-lg">{{ number_format($store->trust_score, 1) }}</span>
            </div>
            <span class="text-green-600">✓ {{ $store->confirmed_orders }} pedidos confirmados</span>
            @if($store->avg_delivery_days > 0)
            <span class="text-blue-600">✓ Entrega média em {{ number_format($store->avg_delivery_days, 1) }} dias</span>
            @endif
            <span class="text-gray-500">✓ {{ $store->total_orders }} clientes</span>
        </div>
    </div>
    @endif

    <!-- Banner -->
    @if($store->cover_image)
    <div class="rounded-xl overflow-hidden mb-6">
        <img src="{{ $store->cover_image }}" alt="{{ $store->name }}" class="w-full h-48 md:h-64 object-cover">
    </div>
    @endif

    <!-- Natural Language Input -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form id="nl-form" class="flex gap-2" onsubmit="event.preventDefault(); interpretQuery();">
            <div class="flex-1">
                <input type="text" id="nl-query" placeholder="💬 O que precisas hoje? Ex: 2 sacos de arroz e 1 garrafão de água"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none text-gray-700">
            </div>
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">
                Enviar
            </button>
        </form>
        <div id="nl-result" class="mt-3 hidden"></div>
    </div>

    <!-- Products Grid -->
    <h2 class="text-xl font-semibold mb-4">Produtos</h2>
    
    @php
        $featured = $store->storeProducts()->visible()->featured()->with('product')->get()->pluck('product')->filter();
    @endphp

    @if($featured->count() > 0)
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
        @foreach($featured as $product)
        <a href="/loja/{{ $store->slug }}/products/{{ $product->id }}" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
            <div class="aspect-square bg-gray-100 flex items-center justify-center p-4">
                @if($product->image)
                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-contain">
                @else
                    <span class="text-4xl text-gray-300">📦</span>
                @endif
            </div>
            <div class="p-3">
                <h3 class="font-medium text-sm text-gray-900 truncate">{{ $product->name }}</h3>
                <p class="text-blue-600 font-bold mt-1">{{ number_format($product->sale_price, 0, ',', ' ') }} AOA</p>
                @php
                    $label = $product->quantity > 5 ? ['text' => 'Disponível', 'class' => 'text-green-600'] : ($product->quantity > 0 ? ['text' => 'Últimas unidades', 'class' => 'text-orange-500'] : ['text' => 'Esgotado', 'class' => 'text-red-500']);
                @endphp
                <span class="text-xs {{ $label['class'] }}">{{ $label['text'] }}</span>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 text-gray-400">
        <p class="text-5xl mb-3">🛍️</p>
        <p>Nenhum produto em destaque ainda.</p>
    </div>
    @endif

    <script>
    function interpretQuery() {
        const query = document.getElementById('nl-query').value.trim();
        const resultDiv = document.getElementById('nl-result');
        
        if (!query) return;
        
        resultDiv.classList.remove('hidden');
        resultDiv.innerHTML = '<p class="text-gray-500">A interpretar...</p>';
        
        fetch('/api/s/{{ $store->slug }}/cart/interpret', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ query })
        })
        .then(r => r.json())
        .then(data => {
            const items = data.data?.items || [];
            const unmatched = data.data?.unmatched || [];
            
            if (items.length > 0) {
                let html = '<div class="bg-green-50 border border-green-200 rounded-lg p-3"><p class="text-green-700 font-medium">✅ Itens identificados:</p><ul class="mt-1 text-sm text-green-600">';
                items.forEach(item => {
                    html += `<li>• ${item.product_name} × ${item.quantity} — ${(item.unit_price * item.quantity).toLocaleString()} AOA</li>`;
                });
                html += '</ul></div>';
                resultDiv.innerHTML = html;
                
                // Update cart count
                if (data.data?.cart_count) {
                    const badge = document.getElementById('cart-count');
                    badge.textContent = data.data.cart_count;
                    badge.classList.remove('hidden');
                }
            } else if (unmatched.length > 0) {
                resultDiv.innerHTML = '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3"><p class="text-yellow-700">⚠ Não encontrei correspondência para: "' + unmatched.join(', ') + '"</p></div>';
            }
        })
        .catch(() => {
            resultDiv.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-600">Erro ao processar. Tenta novamente.</div>';
        });
    }
    </script>
@endsection
