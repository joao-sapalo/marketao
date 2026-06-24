@extends('store.layouts.store')

@section('content')
    <h1 class="text-2xl font-bold mb-6">🛒 Carrinho</h1>

    <div id="cart-content">
        <div class="text-center py-12 text-gray-400">
            <p class="text-5xl mb-3">🔄</p>
            <p>A carregar carrinho...</p>
        </div>
    </div>

    <script>
    function loadCart() {
        fetch('/api/s/{{ $store->slug }}/cart')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('cart-content');
                const items = data.data?.items || [];
                
                if (items.length === 0) {
                    container.innerHTML = '<div class="text-center py-16 text-gray-400"><p class="text-5xl mb-3">🛒</p><p>Carrinho vazio.</p><a href="/loja/{{ $store->slug }}/products" class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Ver Produtos</a></div>';
                    return;
                }

                let html = '<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">';
                html += '<div class="divide-y divide-gray-200">';
                
                items.forEach(item => {
                    html += `<div class="p-4 flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900">${item.name}</h3>
                            <p class="text-sm text-gray-500">${item.quantity} × ${item.sale_price.toLocaleString()} AOA</p>
                        </div>
                        <p class="font-bold text-blue-600">${item.subtotal.toLocaleString()} AOA</p>
                    </div>`;
                });
                
                html += '</div>';
                html += `<div class="p-4 bg-gray-50 flex items-center justify-between">
                    <span class="font-semibold text-lg">Total</span>
                    <span class="font-bold text-xl text-blue-600">${data.data.total.toLocaleString()} AOA</span>
                </div>`;
                html += '</div>';
                
                html += `<div class="mt-6 text-center">
                    <a href="/loja/{{ $store->slug }}/products" class="inline-block px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 mr-3">Continuar a Comprar</a>
                    <button onclick="checkout()" class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-lg">Finalizar Pedido</button>
                </div>`;
                
                container.innerHTML = html;
            })
            .catch(() => {
                document.getElementById('cart-content').innerHTML = '<div class="text-center py-12 text-red-500">Erro ao carregar carrinho.</div>';
            });
    }

    function checkout() {
        window.location.href = '/loja/{{ $store->slug }}/orders/new';
    }

    loadCart();
    </script>
@endsection
