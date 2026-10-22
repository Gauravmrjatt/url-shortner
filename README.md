# URL Shortener

A small URL shortener built on Express, MongoDB and server-rendered EJS views, with click analytics and a Docker setup.

## Features

- Create short links from a web form
- Redirect from `GET /s/:code` to the destination
- **Click tracking** — every visit is recorded in a `ClickLog` collection
- Per-link stats page
- "My links" view for the links you created
- Password-gated admin/management pages
- Runs with Docker Compose

## Stack

Node.js · Express · MongoDB (Mongoose) · EJS · Docker

## Structure

```
server.js           routes and app setup
models/
  Url.js            short link documents
  ClickLog.js       click events
services/
  database.js       MongoDB connection
  UrlService.js     link creation and lookup
views/              EJS templates (index, login, my-links, short, stats)
```

## Routes

| Method | Path | Purpose |
|---|---|---|
| `GET` | `/` | Create a short link |
| `GET` | `/my-links` | Links you created |
| `GET` | `/short` | Shorten form |
| `POST` | `/short` | Submit a URL |
| `GET` | `/stats` | Per-link click stats |
| `GET` | `/s/:code` | Redirect and log the click |
| `POST` | `/api/shorten` | JSON API for shortening |

## Getting started

```bash
npm install
```

Create `.env` (do not commit it):

```env
MONGO_URI=<your-mongodb-connection-string>
BASE_URL=http://localhost:3000
ADMIN_PASSWORD=<choose-a-strong-password>
```

```bash
npm run dev        # node server.js
```

### With Docker

```bash
docker compose up
```

## Environment variables

| Variable | Purpose |
|---|---|
| `MONGO_URI` | MongoDB connection string (see `services/database.js`) |
| `BASE_URL` | Origin used when building short links |
| `ADMIN_PASSWORD` | Password for the management views |

---

> **Security note:** `server.js` falls back to a hardcoded password when `ADMIN_PASSWORD` is unset. Set the variable explicitly in every environment, and remove the literal fallback from the source.
