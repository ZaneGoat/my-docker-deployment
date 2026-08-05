# My Docker Deployment

**A.K.A. "The Circus of Containers" or "How I Learned to Stop Worrying and Love Docker Compose"**

## What Is This Madness?

This is a single Docker Compose file that runs roughly _a million_ web apps on one machine like some kind of digital Jenga tower. It features:

- **1 Nginx** — The bouncer. Decides where traffic goes so your apps don't fight.
- **1 PHP-FPM** — Because someone still uses PHP and we respect their choices.
- **4 Flask apps** — You can never have too many Python micro-frameworks.
- **1 Django app** — The overachiever of the group.
- **1 MySQL** — Where all your data goes to sleep (and sometimes never wakes up).
- **1 Ngrok** — Because exposing localhost to the world is _definitely_ a good idea.

Total: **8 containers**, zero chill.

## Services & Their Ports

| Service | Route | What It Does |
|---|---|---|
| PHP apps | `/` | ayarestoPHP, projrtZL, and the legendary Database PHP |
| Ihsan | `/ihsan/` | Flask App #1 |
| Traiteur | `/traiteur/` | Flask App #2 — catering, probably |
| Khadija | `/khadija/` | Flask App #3 — Patisserie (fancy) |
| Terrain | `/terrain/` | Flask App #4 — book your soccer field, I guess |
| IPIRNET | `/ipirnet/` | Django App #5 — the one with _requirements.txt_ |

## How To Run This Beautiful Disaster

```bash
docker compose up -d
```

That's it. Go touch grass.

## How To Stop The Chaos

```bash
docker compose down
```

## How To Nuke Everything (Including Your Data)

```bash
docker compose down -v
```

RIP `db_data`.

## Architecture (If You Can Call It That)

```
Internet -> Ngrok -> Nginx (port 80) -> PHP-FPM / Flask 1-4 / Django
```

Everything is on `network_mode: "host"` because **bridges are for hobbits, not containers**.

## Secrets

There's an Ngrok auth token in the docker-compose.yml. Yes, it's in plaintext. No, I'm not proud of it. Delete it before you push this to GitHub. You've been warned.

## FAQ

**Q: Why is everything on host network mode?**
A: Port mapping is for people who have their lives together.

**Q: Why 4 Flask apps?**
A: Why does a glizzy have 4 flavors? Some questions are beyond science.

**Q: MySQL with an empty root password?**
A: In production? Absolutely not. In this circus? We live on the edge.

**Q: Is this production-ready?**
A: That depends. Do you consider chaos production-ready?

## License

WTFL — Whatever The F*** License. Do what you want, just don't blame me when your server goes up in flames.
