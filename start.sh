#!/bin/bash
echo "========== MarketAO ERP - Iniciando =========="
echo ""
echo "Iniciando Backend (Laravel) na porta 8000..."
export PHP_INI_SCAN_DIR="/etc/php/8.3/cli/conf.d:/home/js/.php/conf.d"
export PATH="$HOME/.local/bin:$PATH"

cd "$(dirname "$0")"

# Clear cache and optimize
php artisan optimize:clear 2>/dev/null

# Database migrations and seed
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force

# Start Laravel
php artisan serve --port=8000 &
BACKEND_PID=$!

echo "Iniciando Frontend (React) na porta 5173..."
cd frontend
npm run dev -- --host 0.0.0.0 &
FRONTEND_PID=$!

echo ""
echo "Backend:  http://localhost:8000"
echo "Frontend: http://localhost:5173"
echo ""
echo "Login: admin@marketao.com / password"
echo ""
echo "Pressione CTRL+C para parar tudo"

trap "kill $BACKEND_PID $FRONTEND_PID 2>/dev/null; exit" SIGINT SIGTERM
wait
