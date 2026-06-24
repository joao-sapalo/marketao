@extends('store.layouts.store')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="md:flex">
            <div class="md:w-1/2 bg-gray-50 p-8 flex items-center justify-center">
                @if($product->image)
                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="max-h-80 object-contain">
                @else
                    <span class="text-8xl text-gray-300">📦</span>
                @endif
            </div>
            <div class="md:w-1/2 p-6 md:p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                @if($product->code)
                    <p class="text-sm text-gray-500 mb-4">Código: {{ $product->code }}</p>
                @endif
                <p class="text-3xl font-bold text-blue-600 mb-4">{{ number_format($product->sale_price, 0, ',', ' ') }} AOA</p>
                
                <div class="mb-4">
                    @php
                        $stockLabel = $product->quantity > 5 ? ['text' => 'Disponível', 'class' => 'text-green-600 bg-green-50 border-green-200'] : ($product->quantity > 0 ? ['text' => 'Últimas unidades', 'class' => 'text-orange-500 bg-orange-50 border-orange-200'] : ['text' => 'Esgotado', 'class' => 'text-red-500 bg-red-50 border-red-200']);
                    @endphp
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-medium border {{ $stockLabel['class'] }}">
                        {{ $stockLabel['text'] }}
                    </span>
                </div>

                @if($product->quantity > 0)
                <div class="flex items-center gap-4 mb-6">
                    <button onclick="changeQty(-1)" class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-100 text-lg font-medium">−</button>
                    <input type="number" id="qty" value="1" min="1" max="{{ $product->quantity }}" class="w-16 text-center border border-gray-300 rounded-lg py-2 text-lg font-medium" readonly>
                    <button onclick="changeQty(1)" class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-100 text-lg font-medium">+</button>
                </div>

                <button onclick="addToCart()" class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-lg transition-colors">
                    Adicionar ao Carrinho
                </button>
                @endif

                @if($product->description)
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-2">Descrição</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ $product->description }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
    function changeQty(delta) {
        const input = document.getElementById('qty');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        if (val > {{ $product->quantity }}) val = {{ $product->quantity }};
        input.value = val;
    }

    function addToCart() {
        const qty = parseInt(document.getElementById('qty').value);
        
        fetch('/api/s/{{ $store->slug }}/cart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ product_id: {{ $product->id }}, quantity: qty })
        })
        .then(r => r.json())
        .then(data => {
            if (data.item_count) {
                const badge = document.getElementById('cart-count');
                badge.textContent = data.item_count;
                badge.classList.remove('hidden');
            }
            alert('✅ Adicionado ao carrinho!');
        })
        .catch(() => alert('Erro ao adicionar.'));
    }
    </script>
@endsection
