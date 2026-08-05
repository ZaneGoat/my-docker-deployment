# lessons

## Start-Web-Portal.bat — podman -> docker fix

**Problem:** Script tried to start podman service and used `DOCKER_HOST=unix:///run/podman/podman.sock`, but docker is running natively in wsl kali-linux.

**Fix:** Removed podman service startup, changed to `docker compose up -d` directly.

**Detection:** Check `docker info` and `service docker status` in wsl to confirm docker is native before debugging compose.

## MySQL — create database / import schema for php apps

**Problem:** Php app connects to `my_project` database but it doesn't exist in mysql container.

**Fix:** Write a .sh script to disk, then run it via `wsl -d kali-linux -u root -e bash script.sh`. Avoid inline bash `-c` commands with nested quoting — powershell + wsl quoting is a minefield. Use heredocs or `|` pipes inside a script file instead.

**Detection:** `docker exec -i "$cid" mysql -uroot -B -e "SQL"` with `-B` for batch mode shows tabular output.

## Flask apps crash at startup — missing mysql databases

**Problem:** Flask apps using sqlalchemy fail with `OperationalError: (1049, "Unknown database 'xxx'")` because the database doesn't exist in mysql. Containers exit immediately with code 1.

**Fix:** Create the missing databases in mysql (`CREATE DATABASE IF NOT EXISTS dbname;`), then restart the containers. Flask apps create their own tables via sqlalchemy on first run — they only need the db to exist.

**Detection:** `docker logs <container>` shows the sqlalchemy error. `docker ps -a` shows `Exited (1)`.

## Ngrok tunnel ERR_NGROK_3200

**Problem:** Accessing the ngrok URL returns `ERR_NGROK_3200` (Tunnel not found) despite the container running. This happens if `docker-compose.yml` hardcodes an outdated or improperly formatted `--url` flag.

**Fix:** Remove the `--url=...` flag from the ngrok command in `docker-compose.yml` (e.g., use `command: http 127.0.0.1:80`). Ngrok will automatically assign the correct static domain linked to the authtoken. Use `get_ngrok.sh` to fetch the assigned URL dynamically.

**Detection:** `curl -I <ngrok_url>` returns 404 with `ERR_NGROK_3200`. Ngrok logs (if `--log stdout` is added) might still show it binding, but the edge servers reject it.
