#!/usr/bin/env bash
curl -s http://127.0.0.1:4040/api/tunnels 2>/dev/null | grep -o 'https://[^"]*ngrok[^"]*' | head -n 1 > /tmp/ngrok_url.txt
