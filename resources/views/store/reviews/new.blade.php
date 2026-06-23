@extends('store.layouts.store')

@section('content')
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h1 class="text-2xl font-bold mb-2">Avaliar Pedido</h1>
            <p class="text-gray-500 text-sm mb-6">{{ $order->reference }}</p>

            @if($order->storeReview)
            <div class="text-center py-8">
                <p class="text-5xl mb-3">✅</p>
                <p class="text-gray-600">Já avaliaste este pedido. Obrigado!</p>
            </div>
            @elseif($order->status !== 4)
            <div class="text-center py-8">
                <p class="text-5xl mb-3">⏳</p>
                <p class="text-gray-600">Só podes avaliar após a entrega do pedido.</p>
            </div>
            @else
            <form id="review-form">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">A tua avaliação</label>
                    <div class="flex gap-2 text-3xl" id="star-rating">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" onclick="setRating({{ $i }})" class="star text-gray-300 hover:text-yellow-400 transition-colors" data-value="{{ $i }}">★</button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating" value="0">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Comentário (opcional)</label>
                    <textarea name="comment" id="comment" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Partilha a tua experiência..."></textarea>
                </div>

                <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    Enviar Avaliação
                </button>
            </form>
            @endif
        </div>
    </div>

    <script>
    let selectedRating = 0;

    function setRating(val) {
        selectedRating = val;
        document.getElementById('rating').value = val;
        document.querySelectorAll('.star').forEach((star, i) => {
            star.classList.toggle('text-yellow-400', i < val);
            star.classList.toggle('text-gray-300', i >= val);
        });
    }

    document.getElementById('review-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (selectedRating === 0) {
            alert('Selecciona uma avaliação de 1 a 5 estrelas.');
            return;
        }

        fetch('/api/s/{{ $store->slug }}/orders/{{ $order->id }}/review', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                rating: selectedRating,
                comment: document.getElementById('comment').value
            })
        })
        .then(r => r.json())
        .then(data => {
            alert('✅ ' + (data.message || 'Avaliação enviada!'));
            location.reload();
        })
        .catch(() => alert('Erro ao enviar avaliação.'));
    });
    </script>
@endsection
