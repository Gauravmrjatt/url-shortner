# URL Shortener

A small URL shortener on Express, MongoDB and EJS. Paste a link, get a short one, watch the clicks come in.

## What it does

- Shorten a URL from a form or from `POST /api/shorten`
- `GET /s/:code` resolves the code and redirects
- Each redirect writes one click record, so the numbers stay exact rather than sampled
- Per link stats page
- A "my links" view for links you created
- Management views behind a password
- Runs with Docker Compose

About 970 lines across 10 files: 8 routes, 2 collections.

Short codes carry a unique index, so a redirect is a single lookup instead of a scan. Click records are indexed by link and by time, which keeps the stats queries cheap as the collection grows.

## Stack

Node.js, Express, MongoDB via Mongoose, EJS, Docker.

## Layout

```
server.js           routes and app setup
models/
  Url.js            short link documents
  ClickLog.js       one document per click
services/
  database.js       Mongo connection
  UrlService.js     create and resolve
views/              EJS templates
```

## Routes

| Method | Path | What it does |
|---|---|---|
| `GET` | `/` | Shorten form |
| `GET` | `/my-links` | Links you created |
| `GET` | `/short` | Shorten page |
| `POST` | `/short` | Submit a URL |
| `GET` | `/stats` | Per link stats |
| `GET` | `/s/:code` | Redirect, and log the click |
| `POST` | `/api/shorten` | JSON API |

## Run it

```bash
npm install
```

Create `.env` (do not commit it):

```env
MONGO_URI=<your-mongo-connection-string>
BASE_URL=http://localhost:3000
ADMIN_PASSWORD=<choose-a-strong-password>
```

```bash
npm run dev        # node server.js
```

With Docker:

```bash
docker compose up
```

## Environment

| Variable | Purpose |
|---|---|
| `MONGO_URI` | Mongo connection string, see `services/database.js` |
| `BASE_URL` | Origin used when building short links |
| `ADMIN_PASSWORD` | Password for the management views |

## Known issue

`server.js` falls back to a hardcoded password when `ADMIN_PASSWORD` is unset. Set the variable in every environment and delete the literal fallback.
