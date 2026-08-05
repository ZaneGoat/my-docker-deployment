#!/bin/bash
cd /home/Zane/Desktop/my-docker-deployment || exit 1

echo "[*] Checking local Docker images..."
if ! docker images | grep -q 'my-flask-base'; then
    echo "[-] Base image missing. Building localhost/my-flask-base..."
    docker build -t localhost/my-flask-base -f Dockerfile.flask .
else
    echo "[+] Base image already exists."
fi

echo "[*] Starting Docker Compose services..."
docker compose up -d

echo "[*] Provisioning databases..."
# In case the volume was already populated before our init-dbs.sql was mounted,
# force create them manually on the running container just in case.
docker exec my-docker-deployment-mysql-1 mysql -uroot -e "
CREATE DATABASE IF NOT EXISTS ihsan;
CREATE DATABASE IF NOT EXISTS patisserie;
CREATE DATABASE IF NOT EXISTS traiteur;
CREATE DATABASE IF NOT EXISTS ayaresto;
CREATE DATABASE IF NOT EXISTS projrtzl;" || echo "[-] Warning: Manual DB creation failed (might be starting up still). Relying on entrypoint script."

echo "[*] Waiting for Ngrok tunnel..."
MAX_RETRIES=15
URL=""
for ((i=1; i<=MAX_RETRIES; i++)); do
    URL=$(curl -s http://127.0.0.1:4040/api/tunnels | grep -o '"public_url":"[^"]*' | grep -o 'https://[^"]*' | head -n 1)
    if [ -n "$URL" ]; then
        echo "$URL" > /tmp/ngrok_url.txt
        echo "[+] Ngrok tunnel established: $URL"
        break
    fi
    echo "    - Still waiting for Ngrok ($i/$MAX_RETRIES)..."
    sleep 1
done

if [ -z "$URL" ]; then
    echo "ERROR: Ngrok failed to start or bind within 15 seconds." > /tmp/ngrok_url.txt
fi
