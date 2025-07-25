#!/bin/sh

# Токен бота
BOT_TOKEN=""

# Домен
DOMAIN="https://domain.com"

echo "Ожидание запуска веб-сервера..."
sleep 10

# Закомментированная логика для ngrok (для разработки)
#echo "Ожидание запуска ngrok..."
#sleep 5
#
#NGROK_URL=""
#for i in {1..30}; do
#    NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o 'https://[^"]*\.ngrok[^"]*' | head -1)
#    if [ ! -z "$NGROK_URL" ]; then
#        break
#    fi
#    echo "Попытка $i/30 получить ngrok URL..."
#    sleep 2
#done
#
#if [ -z "$NGROK_URL" ]; then
#    echo "ОШИБКА: Не удалось получить ngrok URL"
#    exit 1
#fi
#
#echo "Ngrok URL: $NGROK_URL"
#WEBHOOK_URL="$NGROK_URL/telegraph/$BOT_TOKEN/webhook"

echo "Используется домен: $DOMAIN"
WEBHOOK_URL="$DOMAIN/telegraph/$BOT_TOKEN/webhook"
echo "Устанавливаем вебхук: $WEBHOOK_URL"

RESPONSE=$(curl -s -X POST "https://api.telegram.org/bot$BOT_TOKEN/setWebhook" \
     -H "Content-Type: application/json" \
     -d "{\"url\":\"$WEBHOOK_URL\"}")

if echo "$RESPONSE" | grep -q '"ok":true'; then
    echo "Вебхук успешно установлен!"
else
    echo "ОШИБКА установки вебхука:"
    echo "$RESPONSE"
fi

echo "Статус вебхука:"
curl -s "https://api.telegram.org/bot$BOT_TOKEN/getWebhookInfo" | grep -o '"url":"[^"]*"'
