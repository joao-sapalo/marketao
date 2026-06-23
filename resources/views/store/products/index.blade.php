@extends('store.layouts.store')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Produtos</h1>
        <form method="GET" action="/loja/{{ $store->slug }}/products" class="flex gap-2">
            <input type="text" name="search" placeholder="Pesquisar..." value="{{ request('search') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">🔍</button>
        </form>
    </div>

    @if($products->count() > 0)
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($products as $product)
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

    <div class="mt-8">
        {{ $products->links() }}
    </div>
    @else
    <div class="text-center py-16 text-gray-400">
        <p class="text-5xl mb-3">🔍</p>
        <p>Nenhum produto encontrado.</p>
    </div>
    @endif
@endsection
