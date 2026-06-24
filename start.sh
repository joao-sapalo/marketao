#!/bin/bash
echo "========== MarketAO ERP - Iniciando =========="
echo ""

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

# ── Backend ──
echo "Iniciando Backend (Laravel) na porta 8000..."
cd "$PROJECT_DIR/backend"
php artisan optimize:clear 2>/dev/null
php artisan serve --port=8000 &
BACKEND_PID=$!

# ── Dashboard ──
echo "Iniciando Dashboard (React) na porta 5173..."
cd "$PROJECT_DIR/marketao-frontend"
npm run dev -- --host 0.0.0.0 &
DASHBOARD_PID=$!

# ── Storefront ──
echo "Iniciando Storefront (React) na porta 5174..."
cd "$PROJECT_DIR/storefront"
npm run dev -- --host 0.0.0.0 &
STOREFRONT_PID=$!

cd "$PROJECT_DIR"

echo ""
echo "Backend:     http://localhost:8000"
echo "Dashboard:   http://localhost:5173"
echo "Storefront:  http://localhost:5174"
echo ""
echo "Login: admin@marketao.com / password"
echo ""
echo "Pressione CTRL+C para parar tudo"

trap "kill $BACKEND_PID $DASHBOARD_PID $STOREFRONT_PID 2>/dev/null; exit" SIGINT SIGTERM
wait
