# MarketAO — Módulo de Loja (Storefront v2)

> Especificação técnica completa para implementação da loja pública.
> Versão redesenhada para resolver os problemas reais do comércio angolano,
> não apenas replicar padrões ocidentais de e-commerce.

---

## 0. Diagnóstico: Por que o e-commerce falha em Angola

Antes de arquitectar, é preciso entender o problema real. Os três bloqueios que destroem a conversão no comércio digital angolano:

| Bloqueio | Causa Real | O que a maioria faz | O que vamos fazer |
|---|---|---|---|
| **Desconfiança** | Cliente não sabe se o comerciante é real | Mostrar nome e foto | Sistema de reputação com métricas verificáveis |
| **Fricção no pagamento** | Multicaixa é manual, transferência é incerta | Mostrar dados bancários e torcer | Confirmação de pagamento guiada com referência única |
| **Comunicação falha** | Pedido feito, silêncio total | Email automático genérico | WhatsApp-first: cada evento do pedido vai para o WhatsApp do cliente |

Esta especificação resolve os três.

---

## 1. Visão do Produto

**MarketAO Loja** não é uma loja online genérica. É uma **vitrine de confiança** — o cliente vê o que o comerciante vende, vê a reputação do comerciante, faz o pedido, paga com segurança e acompanha em tempo real. O comerciante recebe tudo no dashboard e gere sem sair do ERP.

```
COMERCIANTE                          CLIENTE
     │                                   │
     │  cria conta no ERP                │
     │──────────────────────────────────►│ loja.marketao.ao/joao-loja
     │                                   │ navega → confia → pede
     │◄──────────────────────────────────│
     │  notificação em tempo real        │
     │  confirma / gere no dashboard     │
     │──────────────────────────────────►│ recebe update no WhatsApp
     │                                   │ rastreia o pedido
```

---

## 2. Inovações Core

### 2.1 — Score de Confiança do Comerciante

Cada loja tem um **Trust Score** calculado automaticamente, visível para o cliente **antes do checkout**.

```
┌─────────────────────────────────────────┐
│  Loja do João  ★ 4.8                    │
│  ────────────────────────────────────── │
│  ✓ 98% dos pedidos confirmados          │
│  ✓ Entrega média em 1.2 dias            │
│  ✓ 142 clientes satisfeitos             │
│  ✓ Membro desde Jan 2025                │
└─────────────────────────────────────────┘
```

Calculado a partir de:
- Taxa de confirmação de pedidos (pendentes confirmados / total)
- Tempo médio entre pedido e confirmação
- Taxa de cancelamento (penaliza)
- Número de pedidos entregues
- Avaliações dos clientes

Fórmula (service dedicado, calculado em background):
```
trust_score = (
  (confirmation_rate * 40) +
  (delivery_speed_score * 30) +
  (review_average * 20) +
  (volume_score * 10)
) / 100
```

**Por que isto é inovador:** nenhum ERP angolano expõe métricas de confiança ao cliente. O cliente angolano decide comprar baseado em quem conhece — isto replica essa lógica digitalmente.

---

### 2.2 — Pedido por Linguagem Natural (Chat Intent)

Em vez de obrigar o cliente a navegar catálogo → produto → carrinho → checkout, a loja tem uma barra de intenção:

```
┌────────────────────────────────────────────────────────┐
│  💬 O que precisas hoje?                               │
│  "2 sacos de arroz e 1 garrafão de água"          [→]  │
└────────────────────────────────────────────────────────┘
```

O input é enviado para a API Anthropic (claude-sonnet-4-6) com o catálogo da loja como contexto. O modelo retorna os produtos identificados + quantidades. O cliente confirma e os itens vão directamente para o carrinho.

Isto resolve o problema do cliente mobile que não quer navegar — digita como faz no WhatsApp.

**Fallback:** se o modelo não encontrar produto correspondente, mostra sugestões do catálogo filtradas por termos aproximados.

---

### 2.3 — WhatsApp-First Notifications

Cada evento do pedido dispara uma mensagem WhatsApp para o cliente **e** para o comerciante. Email é fallback.

| Evento | Mensagem para Cliente | Mensagem para Comerciante |
|---|---|---|
| Pedido criado | "Recebemos o teu pedido ORD-001. Total: 15.000 AOA. Aguarda confirmação." | "Novo pedido de Maria Silva — 15.000 AOA" |
| Pedido confirmado | "Pedido confirmado! Referência de pagamento: [dados]" | — |
| Pagamento recebido | "Pagamento recebido. O teu pedido está em preparação." | "Pagamento confirmado para ORD-001" |
| Pedido entregue | "Pedido entregue! Avalia a tua experiência: [link]" | — |
| Pedido cancelado | "O teu pedido foi cancelado. Motivo: [X]" | — |

Integração via **WhatsApp Business Cloud API** (Meta) — gratuito até 1000 conversas/mês.

```ruby
# app/services/notifications/whatsapp_service.rb
class Notifications::WhatsappService
  TEMPLATE_MAP = {
    order_created:   'order_received',
    order_confirmed: 'order_confirmed',
    order_delivered: 'order_delivered',
    order_cancelled: 'order_cancelled'
  }.freeze

  def initialize(phone:, event:, variables: {})
    @phone     = normalize_phone(phone)   # +244XXXXXXXXX
    @event     = event
    @variables = variables
  end

  def call
    return if @phone.blank?
    WhatsappNotificationJob.perform_later(
      phone:     @phone,
      template:  TEMPLATE_MAP[@event],
      variables: @variables
    )
  end

  private

  def normalize_phone(phone)
    # Converte 9XXXXXXXX → +2449XXXXXXXX
    cleaned = phone.gsub(/\D/, '')
    cleaned.start_with?('244') ? "+#{cleaned}" : "+244#{cleaned}"
  end
end
```

---

### 2.4 — Dashboard Preditivo (Insights em Tempo Real)

O dashboard do comerciante não mostra só dados — **diz o que fazer**.

```
┌──────────────────────────────────────────────────────────┐
│  💡 Insights de hoje                                     │
│  ──────────────────────────────────────────────────────  │
│  ⚠ Arroz 5kg vai esgotar em ~3 dias ao ritmo actual     │
│  📈 Sexta-feira é o teu melhor dia — tens stock?        │
│  👤 Carlos Mendes não compra há 18 dias (média: 12)     │
│  💰 Contas a receber em atraso: 45.000 AOA — 3 clientes │
└──────────────────────────────────────────────────────────┘
```

Gerado por:
- `StockForecastService` — calcula dias restantes por produto com base na velocidade de venda
- `SalePatternService` — detecta picos por dia da semana / hora
- `CustomerChurnService` — identifica clientes fora do padrão de compra
- `FinancialAlertService` — contas em atraso + vencidas hoje

Estes são **Query Objects simples** — sem ML, sem magia. Aritmética sobre os dados já existentes no ERP.

---

### 2.5 — Confirmação de Pagamento Guiada

O fluxo de pagamento por transferência / Multicaixa é o maior ponto de atrito. A loja resolve com uma **referência única de pagamento** e um wizard de confirmação.

```
FLUXO DE PAGAMENTO (Transferência Bancária):

Passo 1 — Cliente faz pedido
  → Sistema gera referência única: PAG-2025-001-A3X
  → WhatsApp com dados bancários + referência

Passo 2 — Cliente faz transferência e carrega comprovativo
  → Upload de imagem/PDF na página do pedido
  → Order#payment_status → :pending_verification

Passo 3 — Comerciante vê no dashboard
  → Comprovativo visível no detalhe do pedido
  → [Confirmar Pagamento] → payment_status: :paid
  → Order passa para :confirmed automaticamente

Passo 4 — WhatsApp para cliente
  → "Pagamento verificado. Pedido em preparação."
```

Para Multicaixa Express (futuro):
- Integração directa com Multicaixa API quando disponível
- Por agora: mesmo fluxo de comprovativo

---

## 3. Models

### 3.1 `Store`

```ruby
# Campos:
#   id                  :bigint
#   user_id             :bigint        FK → users (unique)
#   name                :string        NOT NULL
#   slug                :string        NOT NULL, unique, [a-z0-9\-]
#   description         :text
#   phone               :string
#   whatsapp            :string        Pode ser diferente do telefone principal
#   email               :string
#   address             :string
#   city                :string
#   is_active           :boolean       default: true
#   primary_color       :string        default: '#2563EB'
#   accepts_cash        :boolean       default: true
#   accepts_transfer    :boolean       default: true
#   accepts_multicaixa  :boolean       default: false
#   bank_name           :string
#   bank_holder         :string
#   bank_iban           :string
#   trust_score         :decimal(4,2)  default: 0.0 (calculado em background)
#   total_orders        :integer       default: 0  (counter cache)
#   confirmed_orders    :integer       default: 0  (counter cache)
#   avg_delivery_days   :decimal(4,2)  default: 0.0
#   created_at          :datetime
#   updated_at          :datetime

belongs_to :user
has_many   :store_products, dependent: :destroy
has_many   :products, through: :store_products
has_many   :orders, dependent: :destroy
has_many   :store_reviews, dependent: :destroy
has_one_attached :logo
has_one_attached :banner
```

---

### 3.2 `StoreProduct`

```ruby
# Campos:
#   id              :bigint
#   store_id        :bigint        FK → stores
#   product_id      :bigint        FK → products
#   is_visible      :boolean       default: true
#   featured        :boolean       default: false
#   display_order   :integer       default: 0
#   created_at      :datetime
#   updated_at      :datetime
#
# Índice único: [store_id, product_id]

belongs_to :store
belongs_to :product

scope :visible,   -> { where(is_visible: true) }
scope :featured,  -> { where(featured: true).order(:display_order) }
scope :ordered,   -> { order(:display_order) }
```

---

### 3.3 `Order`

```ruby
# Campos:
#   id                  :bigint
#   store_id            :bigint        FK → stores
#   customer_id         :bigint        FK → customers (nullable)
#   guest_name          :string
#   guest_phone         :string
#   guest_whatsapp      :string        Opcional, para notificações
#   guest_email         :string
#   status              :integer       enum (ver abaixo)
#   payment_method      :integer       enum
#   payment_status      :integer       enum
#   payment_reference   :string        Referência única gerada (PAG-YYYY-NNNNN-XXX)
#   subtotal            :decimal(15,2)
#   discount            :decimal(15,2) default: 0
#   total               :decimal(15,2)
#   notes               :text
#   reference           :string        Referência do pedido (ORD-YYYY-NNNNN)
#   sale_id             :bigint        FK → sales (preenchido ao confirmar)
#   confirmed_at        :datetime
#   paid_at             :datetime
#   delivered_at        :datetime
#   cancelled_at        :datetime
#   cancel_reason       :text
#   created_at          :datetime
#   updated_at          :datetime
#
# Índices:
#   index_orders_on_store_id
#   index_orders_on_status
#   index_orders_on_reference (unique)
#   index_orders_on_payment_reference (unique)

enum status: {
  pending:    0,   # Pedido recebido, aguarda confirmação do comerciante
  confirmed:  1,   # Comerciante confirmou, aguarda pagamento (ou já pago)
  processing: 2,   # Pagamento verificado, em preparação
  shipped:    3,   # Saiu para entrega
  delivered:  4,   # Entregue ao cliente
  cancelled:  5    # Cancelado
}

enum payment_method: {
  cash:       0,
  transfer:   1,
  multicaixa: 2
}

enum payment_status: {
  unpaid:               0,
  pending_verification: 1,   # Cliente carregou comprovativo, aguarda verificação
  paid:                 2,
  partial:              3,
  refunded:             4
}

has_one_attached :payment_proof   # Comprovativo de transferência
```

---

### 3.4 `OrderItem`

```ruby
# Campos:
#   id              :bigint
#   order_id        :bigint        FK → orders
#   product_id      :bigint        FK → products
#   product_name    :string        Snapshot
#   product_code    :string        Snapshot
#   unit_price      :decimal(15,2) Snapshot
#   quantity        :integer
#   discount        :decimal(15,2) default: 0
#   total           :decimal(15,2)
#   created_at      :datetime
#   updated_at      :datetime

# REGRA: product_name, product_code e unit_price são sempre snapshot.
# Nunca usar product.name em contexto histórico — usar order_item.product_name.
```

---

### 3.5 `StoreReview`

```ruby
# Campos:
#   id              :bigint
#   store_id        :bigint        FK → stores
#   order_id        :bigint        FK → orders (unique — 1 review por pedido)
#   rating          :integer       1..5
#   comment         :text
#   guest_name      :string
#   is_approved     :boolean       default: true (moderação futura)
#   created_at      :datetime
#   updated_at      :datetime

validates :rating, inclusion: { in: 1..5 }
validates :order_id, uniqueness: true
```

---

## 4. Services

### 4.1 `Store::CreateStoreService`

Chamado no `after_create` do `User`. Cria a loja com slug único.

```ruby
class Store::CreateStoreService
  def initialize(user)
    @user = user
  end

  def call
    ActiveRecord::Base.transaction do
      store = Store.create!(
        user:        @user,
        name:        default_name,
        slug:        generate_unique_slug,
        email:       @user.email,
        whatsapp:    @user.telefone,
        phone:       @user.telefone,
        is_active:   false   # Inactiva até o comerciante configurar
      )
      store
    end
  end

  private

  def default_name
    "Loja de #{@user.nome}"
  end

  def generate_unique_slug
    base = default_name.parameterize
    candidate = base
    n = 1
    while Store.exists?(slug: candidate)
      candidate = "#{base}-#{n}"
      n += 1
    end
    candidate
  end
end
```

**Nota:** A loja começa `is_active: false`. O comerciante só activa depois de configurar nome, logo e pelo menos 1 método de pagamento. Isto evita lojas vazias públicas.

---

### 4.2 `Store::NaturalLanguageCartService`

Recebe texto livre do cliente, consulta a API Anthropic com o catálogo da loja, retorna itens identificados.

```ruby
class Store::NaturalLanguageCartService
  def initialize(store:, query:)
    @store = store
    @query = query
  end

  def call
    products_context = build_products_context
    response         = call_anthropic_api(products_context)
    parse_response(response)
  end

  private

  def build_products_context
    @store.store_products.visible.includes(:product).map do |sp|
      p = sp.product
      {
        id:    p.id,
        name:  p.nome,
        code:  p.codigo,
        price: p.preco_venda,
        stock: p.quantidade > 0 ? 'disponível' : 'esgotado'
      }
    end.to_json
  end

  def call_anthropic_api(products_context)
    # Chamada à API — ver secção 8 para implementação completa
    AnthropicApiService.call(
      system: system_prompt(products_context),
      user:   @query
    )
  end

  def system_prompt(products_context)
    <<~PROMPT
      És um assistente de loja angolana. O cliente vai dizer o que quer comprar
      em linguagem natural (Português ou calão angolano).

      Catálogo disponível (JSON):
      #{products_context}

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
    PROMPT
  end

  def parse_response(response)
    data = JSON.parse(response)
    {
      items:     resolve_items(data['items'] || []),
      unmatched: data['unmatched'] || []
    }
  rescue JSON::ParseError
    { items: [], unmatched: [@query] }
  end

  def resolve_items(raw_items)
    raw_items.filter_map do |item|
      product = @store.products.find_by(id: item['product_id'])
      next unless product

      {
        product_id:   product.id,
        product_name: product.nome,
        unit_price:   product.preco_venda,
        quantity:     [item['quantity'].to_i, 1].max
      }
    end
  end
end
```

---

### 4.3 `Store::CheckoutService`

Valida o carrinho e cria `Order` + `OrderItem`s.

**Responsabilidades (por ordem de execução):**

1. Verificar que a loja está activa
2. Verificar que cada produto está visível na loja
3. Verificar stock suficiente para cada item (com `with_lock` para evitar race condition)
4. Calcular subtotal, desconto, total
5. Gerar referência única do pedido (`ORD-YYYY-NNNNN`)
6. Gerar referência de pagamento única (`PAG-YYYY-NNNNN-XXX`)
7. Criar `Order` com status `pending`
8. Criar `OrderItem`s com snapshot
9. Limpar carrinho da sessão
10. Enfileirar `OrderNotificationJob` (WhatsApp + dashboard)

**Retorno:**
```ruby
Result = Struct.new(:success?, :order, :errors, keyword_init: true)
```

**Geração de referências:**
```ruby
def generate_order_reference
  year    = Date.current.year
  seq     = Order.where('created_at >= ?', Date.current.beginning_of_year).count + 1
  "ORD-#{year}-#{seq.to_s.rjust(5, '0')}"
end

def generate_payment_reference
  "PAG-#{Date.current.year}-#{SecureRandom.alphanumeric(6).upcase}"
end
```

---

### 4.4 `Store::ConfirmOrderService`

Chamado pelo comerciante ao confirmar pedido no dashboard.

**Responsabilidades:**

1. Verificar que `order.status == :pending`
2. Verificar stock novamente (with_lock por produto)
3. Criar `Sale` no ERP com os dados do pedido
4. Criar `SaleItem`s a partir dos `OrderItem`s
5. Criar `StockMovement` de saída por produto
6. Criar `AccountReceivable` se `payment_method != :cash` e `payment_status != :paid`
7. Actualizar `order.status → :confirmed`, `order.confirmed_at`
8. Actualizar `order.sale_id`
9. Registar em `AuditLog`
10. Broadcast via `OrdersChannel` (Turbo Stream no dashboard)
11. Enfileirar `WhatsApp: order_confirmed` para o cliente

---

### 4.5 `Store::VerifyPaymentService`

Novo — chamado quando o comerciante confirma comprovativo.

**Responsabilidades:**

1. Verificar que `payment_status == :pending_verification`
2. Actualizar `payment_status → :paid`, `paid_at`
3. Se `order.status == :confirmed` → actualizar para `:processing`
4. Se `AccountReceivable` existir → marcar como pago
5. Registar em `AuditLog`
6. WhatsApp para cliente: "Pagamento verificado"

---

### 4.6 `Store::TrustScoreCalculatorService`

Recalculado diariamente via Sidekiq scheduled job.

```ruby
class Store::TrustScoreCalculatorService
  WEIGHTS = {
    confirmation_rate:  40,
    delivery_speed:     30,
    review_average:     20,
    volume:             10
  }.freeze

  def initialize(store)
    @store = store
  end

  def call
    score = (
      (confirmation_rate  * WEIGHTS[:confirmation_rate])  +
      (delivery_speed     * WEIGHTS[:delivery_speed])     +
      (review_average     * WEIGHTS[:review_average])     +
      (volume_score       * WEIGHTS[:volume])
    ) / 100.0

    @store.update_columns(
      trust_score:       score.round(2),
      total_orders:      total_orders_count,
      confirmed_orders:  confirmed_orders_count,
      avg_delivery_days: avg_delivery
    )
  end

  private

  def confirmation_rate
    return 0 if total_orders_count.zero?
    (confirmed_orders_count.to_f / total_orders_count * 100).clamp(0, 100)
  end

  def delivery_speed
    # Score inverso: quanto mais rápido, maior o score
    return 50 if avg_delivery.zero?
    case avg_delivery
    when 0..1   then 100
    when 1..2   then 80
    when 2..3   then 60
    when 3..5   then 40
    else             20
    end
  end

  def review_average
    avg = @store.store_reviews.average(:rating)&.to_f || 0
    (avg / 5.0 * 100).clamp(0, 100)
  end

  def volume_score
    # Escala logarítmica: 10 pedidos = 50pts, 100 = 80pts, 500+ = 100pts
    n = confirmed_orders_count
    return 0 if n.zero?
    [Math.log10(n) / Math.log10(500) * 100, 100].min
  end

  def total_orders_count
    @total_orders_count ||= @store.orders.count
  end

  def confirmed_orders_count
    @confirmed_orders_count ||= @store.orders.where.not(status: [:pending, :cancelled]).count
  end

  def avg_delivery
    @avg_delivery ||= begin
      @store.orders.delivered
            .where.not(confirmed_at: nil, delivered_at: nil)
            .average("EXTRACT(EPOCH FROM (delivered_at - confirmed_at)) / 86400")
            &.to_f || 0
    end
  end
end
```

---

### 4.7 `Store::StockForecastService` (Insights)

```ruby
class Store::StockForecastService
  FORECAST_DAYS = 7

  def initialize(store)
    @store = store
  end

  # Retorna produtos com previsão de esgotamento em menos de FORECAST_DAYS dias
  def at_risk_products
    @store.store_products.visible.includes(:product).filter_map do |sp|
      product     = sp.product
      daily_sales = avg_daily_sales(product)
      next if daily_sales.zero?

      days_left = product.quantidade / daily_sales
      next if days_left >= FORECAST_DAYS

      {
        product:   product,
        days_left: days_left.round(1),
        daily_avg: daily_sales.round(1)
      }
    end.sort_by { |r| r[:days_left] }
  end

  private

  def avg_daily_sales(product)
    sold = OrderItem
      .joins(:order)
      .where(orders: { store: @store, status: [:confirmed, :processing, :shipped, :delivered] })
      .where(product_id: product.id)
      .where('orders.created_at >= ?', 30.days.ago)
      .sum(:quantity)

    sold.to_f / 30
  end
end
```

---

## 5. Arquitectura de Ficheiros

```
app/
├── controllers/
│   ├── store/
│   │   ├── base_controller.rb           # Resolve @store pelo slug, 404 se inactiva
│   │   ├── home_controller.rb
│   │   ├── products_controller.rb
│   │   ├── cart_controller.rb
│   │   ├── orders_controller.rb
│   │   ├── reviews_controller.rb        # POST avaliação pós-entrega
│   │   └── payment_proofs_controller.rb # Upload de comprovativo
│   └── dashboard/
│       ├── orders_controller.rb
│       ├── store_products_controller.rb
│       ├── stores_controller.rb
│       └── insights_controller.rb       # API endpoint para os insights
│
├── models/
│   ├── store.rb
│   ├── store_product.rb
│   ├── order.rb
│   ├── order_item.rb
│   └── store_review.rb
│
├── services/
│   └── store/
│       ├── create_store_service.rb
│       ├── natural_language_cart_service.rb
│       ├── checkout_service.rb
│       ├── confirm_order_service.rb
│       ├── verify_payment_service.rb
│       ├── cancel_order_service.rb
│       ├── trust_score_calculator_service.rb
│       └── stock_forecast_service.rb
│
├── query_objects/
│   ├── store_orders_query.rb            # Filtros complexos de pedidos
│   └── store_insights_query.rb          # Dados para o painel de insights
│
├── jobs/
│   ├── order_notification_job.rb        # WhatsApp + AuditLog
│   ├── recalculate_trust_scores_job.rb  # Sidekiq scheduled, diário
│   └── send_review_request_job.rb       # WhatsApp pós-entrega com link de avaliação
│
├── channels/
│   └── orders_channel.rb
│
├── views/
│   ├── store/
│   │   ├── layouts/
│   │   │   └── store.html.erb           # Layout separado, sem nav do dashboard
│   │   ├── home/
│   │   │   └── index.html.erb           # Banner + produtos em destaque + barra NL
│   │   ├── products/
│   │   │   ├── index.html.erb
│   │   │   └── show.html.erb
│   │   ├── cart/
│   │   │   └── show.html.erb
│   │   ├── orders/
│   │   │   ├── new.html.erb             # Checkout com wizard de pagamento
│   │   │   └── show.html.erb            # Rastreio + upload comprovativo
│   │   └── reviews/
│   │       └── new.html.erb
│   └── dashboard/
│       ├── orders/
│       │   ├── index.html.erb
│       │   └── show.html.erb            # Com visualizador de comprovativo
│       ├── stores/
│       │   └── edit.html.erb
│       └── store_products/
│           └── index.html.erb
│
└── javascript/
    └── controllers/
        ├── cart_controller.js            # Stimulus: add/remove/update
        ├── quantity_controller.js        # Stimulus: +/-
        ├── natural_language_controller.js # Stimulus: input NL → API → carrinho
        ├── orders_channel_controller.js  # Stimulus + ActionCable
        ├── payment_wizard_controller.js  # Stimulus: mostra dados conforme método
        └── file_upload_controller.js     # Stimulus: preview de comprovativo
```

---

## 6. Rotas

```ruby
# config/routes.rb

# ─────────────────────────────────────────────────
# LOJA PÚBLICA — acesso por /loja/:store_slug
# ─────────────────────────────────────────────────
scope '/loja/:store_slug', module: 'store', as: 'store' do
  root   'home#index',     as: :home
  get    'pesquisa',       to: 'products#search', as: :search   # Turbo Frame

  resources :products,       only: [:index, :show]
  resource  :cart,           only: [:show, :update, :destroy]
  resources :orders,         only: [:new, :create, :show] do
    resource :payment_proof, only: [:create],  module: 'orders'
    resource :review,        only: [:new, :create], module: 'orders'
  end

  # Endpoint para o NaturalLanguageCartService
  post 'cart/interpret', to: 'cart#interpret', as: :cart_interpret
end

# ─────────────────────────────────────────────────
# DASHBOARD — gestão pelo comerciante
# ─────────────────────────────────────────────────
namespace :dashboard do
  resource  :store,         only: [:show, :edit, :update]
  resources :store_products, only: [:index, :update]
  resources :orders,         only: [:index, :show] do
    member do
      patch :confirm
      patch :verify_payment
      patch :mark_processing
      patch :mark_shipped
      patch :mark_delivered
      patch :cancel
    end
  end
  get 'insights', to: 'insights#show'   # JSON endpoint para o painel
end
```

---

## 7. Fluxo Completo (Estado por Estado)

```
                    ESTADOS DO PEDIDO

  ┌─────────┐   confirma   ┌───────────┐  verifica pgto  ┌────────────┐
  │ PENDING │─────────────►│ CONFIRMED │────────────────►│ PROCESSING │
  └─────────┘              └───────────┘                 └────────────┘
       │                        │                               │
       │ cancela                │ cancela                       │ saiu
       ▼                        ▼                               ▼
  ┌──────────┐            ┌──────────┐                   ┌─────────┐
  │CANCELLED │            │CANCELLED │                   │ SHIPPED │
  └──────────┘            └──────────┘                   └─────────┘
                                                               │
                                                               │ entregue
                                                               ▼
                                                         ┌───────────┐
                                                         │ DELIVERED │
                                                         └───────────┘
                                                               │
                                                    (24h depois, Sidekiq)
                                                               ▼
                                                    WhatsApp: link de review


  ESTADOS DO PAGAMENTO (paralelo ao status do pedido)

  UNPAID → PENDING_VERIFICATION → PAID
                                    ↑
               (cliente carrega comprovativo)
               (comerciante verifica e confirma)
```

---

## 8. ActionCable — Tempo Real

### Canal

```ruby
# app/channels/orders_channel.rb
class OrdersChannel < ApplicationCable::Channel
  def subscribed
    reject unless current_user&.store
    stream_for current_user.store
  end
end
```

### Eventos broadcast

```ruby
# Chamado pelo CheckoutService (novo pedido)
OrdersChannel.broadcast_to(store, {
  event:      'new_order',
  order_id:   order.id,
  reference:  order.reference,
  customer:   order.guest_name,
  total:      ActionController::Base.helpers.number_to_currency(order.total, unit: 'AOA '),
  created_at: order.created_at.strftime('%H:%M')
})

# Chamado pelo VerifyPaymentService (comprovativo carregado)
OrdersChannel.broadcast_to(store, {
  event:     'payment_proof_uploaded',
  order_id:  order.id,
  reference: order.reference
})
```

### Stimulus no dashboard

```javascript
// app/javascript/controllers/orders_channel_controller.js
import { Controller } from "@hotwired/stimulus"
import consumer from "../channels/consumer"

export default class extends Controller {
  static targets = ["badge", "list", "notification"]

  connect() {
    this.channel = consumer.subscriptions.create("OrdersChannel", {
      received: (data) => this.handleEvent(data)
    })
  }

  handleEvent(data) {
    if (data.event === 'new_order') {
      this.incrementBadge()
      this.showToast(`Novo pedido de ${data.customer} — ${data.total}`)
      // Turbo.visit para actualizar a lista sem reload total
    }
    if (data.event === 'payment_proof_uploaded') {
      this.showToast(`Comprovativo carregado para ${data.reference}`)
    }
  }

  incrementBadge() {
    const current = parseInt(this.badgeTarget.textContent || '0')
    this.badgeTarget.textContent = current + 1
    this.badgeTarget.classList.remove('hidden')
  }

  showToast(message) {
    // Turbo Stream toast ou notificação nativa do browser
  }

  disconnect() {
    this.channel.unsubscribe()
  }
}
```

---

## 9. Migrations (ordem de execução)

```bash
# 1. Store
rails g migration CreateStores \
  user:references:uniq name:string slug:string \
  description:text phone:string whatsapp:string email:string \
  address:string city:string is_active:boolean \
  primary_color:string \
  accepts_cash:boolean accepts_transfer:boolean accepts_multicaixa:boolean \
  bank_name:string bank_holder:string bank_iban:string \
  trust_score:decimal total_orders:integer confirmed_orders:integer \
  avg_delivery_days:decimal

# 2. StoreProduct
rails g migration CreateStoreProducts \
  store:references product:references \
  is_visible:boolean featured:boolean display_order:integer

# 3. Order
rails g migration CreateOrders \
  store:references customer:references \
  guest_name:string guest_phone:string guest_whatsapp:string guest_email:string \
  status:integer payment_method:integer payment_status:integer \
  payment_reference:string \
  subtotal:decimal discount:decimal total:decimal \
  notes:text reference:string \
  sale:references \
  confirmed_at:datetime paid_at:datetime \
  delivered_at:datetime cancelled_at:datetime cancel_reason:text

# 4. OrderItem
rails g migration CreateOrderItems \
  order:references product:references \
  product_name:string product_code:string \
  unit_price:decimal quantity:integer discount:decimal total:decimal

# 5. StoreReview
rails g migration CreateStoreReviews \
  store:references order:references:uniq \
  rating:integer comment:text guest_name:string is_approved:boolean
```

**Índices adicionais a adicionar manualmente nas migrations:**

```ruby
add_index :stores,         :slug,              unique: true
add_index :orders,         :reference,         unique: true
add_index :orders,         :payment_reference, unique: true
add_index :orders,         :status
add_index :store_products, [:store_id, :product_id], unique: true
```

---

## 10. Segurança

| Camada | Implementação |
|---|---|
| Autorização loja | `StorePolicy` — comerciante só gere a sua loja |
| Autorização pedidos | `OrderPolicy` — scope por `current_user.store` |
| Rate limiting | Rack::Attack: 60 req/min por IP na loja, 10 req/min no checkout |
| Race condition em stock | `product.with_lock { ... }` no CheckoutService e ConfirmOrderService |
| Slug injection | Regex: `/\A[a-z0-9\-]+\z/` + parametrize no create |
| Comprovativo upload | Active Storage + validação de tipo (image/*, application/pdf) |
| Strong Parameters | Todos os controllers com permit explícito |
| Dados de visitante | Apenas nome + telefone obrigatórios, email opcional |
| WhatsApp API | Token em ENV, nunca hardcoded |

---

## 11. Jobs Sidekiq

```ruby
# Enfileirado pelo CheckoutService
class OrderNotificationJob < ApplicationJob
  queue_as :notifications

  def perform(order_id)
    order = Order.find(order_id)
    store = order.store

    # WhatsApp para o cliente
    Notifications::WhatsappService.new(
      phone:     order.guest_whatsapp || order.guest_phone,
      event:     :order_created,
      variables: { reference: order.reference, total: order.total }
    ).call

    # WhatsApp para o comerciante
    Notifications::WhatsappService.new(
      phone:     store.whatsapp,
      event:     :new_order_merchant,
      variables: { reference: order.reference, customer: order.guest_name }
    ).call
  end
end

# Sidekiq scheduled — todos os dias às 02:00
class RecalculateTrustScoresJob < ApplicationJob
  queue_as :background

  def perform
    Store.find_each do |store|
      Store::TrustScoreCalculatorService.new(store).call
    end
  end
end

# Enfileirado 24h após order.delivered_at
class SendReviewRequestJob < ApplicationJob
  queue_as :notifications

  def perform(order_id)
    order = Order.find(order_id)
    return if order.store_review.present?

    Notifications::WhatsappService.new(
      phone:     order.guest_whatsapp || order.guest_phone,
      event:     :review_request,
      variables: {
        store_name:  order.store.name,
        review_url:  review_url(order)
      }
    ).call
  end
end
```

---

## 12. Interface (Especificação Visual)

### Layout da Loja — `store.html.erb`

Layout completamente separado do dashboard. Sem sidebar do ERP.

```
┌──────────────────────────────────────────────────────────────┐
│  [Logo]  Nome da Loja           [🔍 Pesquisa]  [🛒 2]       │  ← Header fixo
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  💬 O que precisas hoje?                    [Enviar] │   │  ← Barra NL
│  └──────────────────────────────────────────────────────┘   │
│                                                              │
│  ★ 4.8  98% confirmados  142 clientes  Membro há 6 meses   │  ← Trust Score
├──────────────────────────────────────────────────────────────┤
│  [BANNER DA LOJA]                                            │
├──────────────────────────────────────────────────────────────┤
│  Categorias: [Todos] [Alimentação] [Bebidas] [Higiene]       │
├──────────────────────────────────────────────────────────────┤
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐                    │
│  │ Prod │  │ Prod │  │ Prod │  │ Prod │   Grid responsivo   │
│  └──────┘  └──────┘  └──────┘  └──────┘                    │
└──────────────────────────────────────────────────────────────┘
```

### Página de Produto

```
┌─────────────────────────────────────────────────────────┐
│  [Imagem]    Nome do Produto                            │
│              Código: PRD-001                            │
│              5.000 AOA                                  │
│                                                         │
│              ● Disponível                               │
│                                                         │
│              [─] 1 [+]                                  │
│                                                         │
│              [ Adicionar ao Carrinho ]                  │
│                                                         │
│  Descrição do produto...                               │
└─────────────────────────────────────────────────────────┘
```

**Regra de stock:** nunca mostrar quantidade exacta. Mostrar apenas:
- `● Disponível` (stock > 5)
- `⚠ Últimas unidades` (stock entre 1 e 5)
- `✗ Esgotado` (stock = 0, botão desactivado)

### Checkout — Wizard de Pagamento

```
PASSO 1: Os teus dados
  Nome *
  Telefone * (formato: 9XXXXXXXX)
  WhatsApp (para receber actualizações — pré-preenche com telefone)
  Email (opcional)
  Notas

PASSO 2: Como queres pagar?
  ○ Dinheiro na entrega
  ○ Transferência Bancária
      → [aparece] Banco: BFA | Titular: João Silva | IBAN: XXXXXXX
        Referência de pagamento: PAG-2025-A3X7K2
        ⚠ Usa esta referência na descrição da transferência
  ○ Multicaixa Express
      → [aparece] Instruções Multicaixa

PASSO 3: Confirmar
  [Resumo lateral com itens + total]
  [ Fazer Pedido ]
```

### Página de Rastreio `/loja/:slug/orders/:reference`

```
✓ Pedido recebido!
Referência: ORD-2025-00042

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
● Recebido     ○ Confirmado   ○ Em prep.   ○ Entregue
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[Se transferência/multicaixa e pagamento pendente:]
┌────────────────────────────────────────┐
│  💳 Dados de Pagamento                 │
│  Banco: BFA                            │
│  Titular: João Silva                   │
│  IBAN: XXXXXXX                         │
│  Referência: PAG-2025-A3X7K2           │
│                                        │
│  Já pagaste?                           │
│  [ Carregar Comprovativo ]             │
└────────────────────────────────────────┘

Itens do pedido:
  Arroz 5kg × 2 ........... 10.000 AOA
  Água 10L × 1 ..............  5.000 AOA
  Total .................... 15.000 AOA

Dúvidas? Fala com a loja:
  WhatsApp: 923 XXX XXX
```

---

## 13. Testes RSpec

### Factories

```ruby
# spec/factories/stores.rb
FactoryBot.define do
  factory :store do
    association :user
    name        { Faker::Company.name }
    slug        { name.parameterize }
    is_active   { true }
    trust_score { 0.0 }
  end
end

# spec/factories/orders.rb
FactoryBot.define do
  factory :order do
    association :store
    guest_name   { Faker::Name.full_name }
    guest_phone  { "9#{rand(10_000_000..99_999_999)}" }
    status       { :pending }
    payment_method { :cash }
    payment_status { :unpaid }
    subtotal     { 10_000 }
    total        { 10_000 }
    reference    { "ORD-#{Date.current.year}-#{rand(10_000..99_999)}" }
  end
end
```

### Cenários obrigatórios

```
Services:
  Store::CreateStoreService
    ✓ Cria loja com slug único ao registar utilizador
    ✓ Gera slug alternativo se slug base já existe
    ✓ Loja criada com is_active: false

  Store::NaturalLanguageCartService
    ✓ Identifica produto por nome exacto
    ✓ Identifica produto por nome aproximado (arroz → Arroz 5kg)
    ✓ Retorna unmatched para termos sem correspondência
    ✓ Não inclui produtos esgotados

  Store::CheckoutService
    ✓ Cria Order e OrderItems com stock suficiente
    ✓ Retorna erro se produto sem stock
    ✓ Retorna erro se loja inactiva
    ✓ Snapshot de product_name e unit_price correcto
    ✓ Gera referência única de pedido
    ✓ Gera referência única de pagamento
    ✓ Enfileira OrderNotificationJob

  Store::ConfirmOrderService
    ✓ Cria Sale no ERP
    ✓ Cria StockMovement de saída por item
    ✓ Cria AccountReceivable para pagamento não à vista
    ✓ Actualiza order.status para :confirmed
    ✓ Falha se order não está :pending
    ✓ Falha se stock insuficiente (verificação dupla)

  Store::VerifyPaymentService
    ✓ Actualiza payment_status para :paid
    ✓ Avança order.status para :processing
    ✓ Marca AccountReceivable como pago

  Store::TrustScoreCalculatorService
    ✓ Calcula score 0 para loja sem pedidos
    ✓ Score 100 para loja com 100% confirmação e entrega em 1 dia
    ✓ Penaliza taxa de cancelamento

Requests:
  GET /loja/:slug
    ✓ 200 para loja activa
    ✓ 404 para slug inválido
    ✓ 404 para loja inactiva

  POST /loja/:slug/cart/interpret
    ✓ Retorna itens identificados
    ✓ Retorna 422 se query em branco

  POST /loja/:slug/orders
    ✓ Cria pedido com dados válidos
    ✓ Redirige para página de rastreio
    ✓ 422 com erros se dados inválidos

  PATCH /dashboard/orders/:id/confirm
    ✓ Confirma pedido da loja do utilizador autenticado
    ✓ 403 se pedido é de outra loja (Pundit)

Policies:
  OrderPolicy
    ✓ Comerciante só acede a pedidos da sua loja
    ✓ Admin acede a todos os pedidos
```

---

## 14. Seeds

```ruby
# db/seeds/store_seeds.rb

# A loja é criada pelo CreateStoreService no after_create do User.
# Seeds apenas configuram e populam.

store = User.first.store
store.update!(
  name:              "Mercearia do João",
  description:       "Produtos frescos para o seu lar, entrega em Luanda.",
  city:              "Luanda",
  whatsapp:          "923000001",
  primary_color:     "#2563EB",
  is_active:         true,
  accepts_cash:      true,
  accepts_transfer:  true,
  bank_name:         "BFA",
  bank_holder:       "João Sapalo",
  bank_iban:         "AO06.0040.0000.0000.1234.1013.4"
)

# Expõe os 10 primeiros produtos na loja
Product.first(10).each_with_index do |product, i|
  StoreProduct.find_or_create_by!(store: store, product: product) do |sp|
    sp.is_visible    = true
    sp.featured      = i < 3
    sp.display_order = i + 1
  end
end

# Pedido de exemplo
order = Order.create!(
  store:          store,
  guest_name:     "Maria da Silva",
  guest_phone:    "924000002",
  guest_whatsapp: "924000002",
  status:         :delivered,
  payment_method: :cash,
  payment_status: :paid,
  subtotal:       15_000,
  total:          15_000,
  reference:      "ORD-#{Date.current.year}-00001",
  confirmed_at:   2.days.ago,
  delivered_at:   1.day.ago
)

StoreReview.create!(
  store:      store,
  order:      order,
  rating:     5,
  guest_name: "Maria da Silva",
  comment:    "Excelente serviço, entrega rápida!"
)

# Recalcular trust score após seeds
Store::TrustScoreCalculatorService.new(store).call
```

---

## 15. Configuração de Ambiente

```bash
# .env (adicionar ao existente)

# WhatsApp Business Cloud API (Meta)
WHATSAPP_ACCESS_TOKEN=your_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_API_VERSION=v19.0

# Anthropic API (para NaturalLanguageCartService)
ANTHROPIC_API_KEY=your_key_here

# URL base da loja (para links nos WhatsApp)
STORE_BASE_URL=https://loja.marketao.ao
```

---

## 16. Ordem de Implementação

```
FASE 1 — Base (sem UI, sem WhatsApp)
  1.  Migrations na ordem da secção 9
  2.  Models + validações + associations
  3.  Store::CreateStoreService + callback after_create no User
  4.  Seeds básicos + verificar que loja é criada no registo

FASE 2 — Storefront Core
  5.  Rotas da loja (namespace store/)
  6.  Store::BaseController (resolve @store pelo slug, 404 se inactiva)
  7.  Store::HomeController + view (sem barra NL por agora)
  8.  Store::ProductsController + views (index + show com regra de stock)
  9.  Store::CartController + Stimulus cart_controller.js
  10. Store::CheckoutService (sem WhatsApp ainda)
  11. Store::OrdersController (new + create + show)
  12. Turbo Streams no carrinho

FASE 3 — Dashboard de Pedidos
  13. Dashboard::OrdersController (index + show + confirm + cancel)
  14. Store::ConfirmOrderService (Sale + StockMovement)
  15. OrdersChannel (ActionCable)
  16. Stimulus orders_channel_controller.js

FASE 4 — Pagamentos e Comprovativos
  17. Upload de comprovativo (Active Storage)
  18. Store::VerifyPaymentService
  19. Dashboard::OrdersController#verify_payment
  20. Wizard de pagamento no checkout (Stimulus payment_wizard_controller.js)

FASE 5 — Confiança e Inteligência
  21. StoreReview model + controller + view pós-entrega
  22. Store::TrustScoreCalculatorService
  23. RecalculateTrustScoresJob (Sidekiq scheduled)
  24. Trust Score visível na loja pública
  25. Store::StockForecastService
  26. Dashboard::InsightsController + painel de insights

FASE 6 — WhatsApp e Linguagem Natural
  27. Notifications::WhatsappService + jobs
  28. Integração WhatsApp em cada evento do pedido
  29. Store::NaturalLanguageCartService
  30. Barra de linguagem natural na home da loja
  31. Stimulus natural_language_controller.js

FASE 7 — Qualidade
  32. Testes RSpec (factories → models → services → requests → policies)
  33. Pundit policies completas
  34. Rack::Attack rate limiting
  35. AuditLog em todos os pontos críticos
  36. Seeds completos com dados realistas angolanos
```

---

## 17. Decisões de Arquitectura

| Decisão | Escolha | Justificação |
|---|---|---|
| Loja inactiva por defeito | `is_active: false` no create | Evita lojas sem configuração expostas publicamente |
| Trust Score em BD | Colunas na tabela `stores` | Performance — evita N+1 em cada visita à loja |
| Carrinho em sessão | `session[:cart]` | Simplicidade, sem auth obrigatória para comprar |
| NL Cart via API | Anthropic claude-sonnet-4-6 | Acesso a modelo já disponível, sem infra adicional |
| Snapshot em OrderItem | `product_name`, `unit_price` | Imutabilidade histórica — pedido não muda se produto muda |
| Sale só ao confirmar | ConfirmOrderService | Pedido ≠ Venda. Integridade contabilística do ERP |
| Stock baixa ao confirmar | Junto com Sale | Evitar reservas de stock por pedidos não confirmados |
| WhatsApp primeiro | Notificações via WhatsApp | Angola: WhatsApp é o canal de comunicação dominante |
| Referência de pagamento | `PAG-YYYY-XXXXXX` único | Rastreabilidade de transferências sem Multicaixa API |
| Review só por pedido entregue | FK `order_id` único em `StoreReview` | Garante reviews legítimas — 1 pedido = 1 review possível |
| Insights sem ML | Query Objects + aritmética | Implementável agora, sem infra de ML, dados reais angolanos |
