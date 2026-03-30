require('dotenv').config();
const express = require('express');
const path = require('path');
const { connect } = require('./services/database');
const UrlService = require('./services/UrlService');

const app = express();
const urlService = new UrlService();

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'Ahfhfhfwjejbf';
const BASE_URL = process.env.BASE_URL || 'http://localhost:3000';

app.locals.baseUrl = BASE_URL;

function isAuthorized(password) {
  return password === ADMIN_PASSWORD;
}

app.get('/', async (req, res) => {
  const password = req.query.password;
  if (!isAuthorized(password)) {
    return res.redirect(`/my-links?password=${encodeURIComponent(password || '')}`);
  }
  
  const urls = await urlService.getAllUrls();
  const totalClicks = urls.reduce((sum, url) => sum + url.clicks, 0);
  
  res.render('index', { 
    urls, 
    totalClicks,
    password,
    baseUrl: BASE_URL 
  });
});

app.get('/my-links', async (req, res) => {
  const { password, search = '', page = 1 } = req.query;
  
  if (!isAuthorized(password)) {
    return res.render('login', { baseUrl: BASE_URL });
  }
  
  const limit = 10;
  const offset = (parseInt(page) - 1) * limit;
  
  const urls = await urlService.searchUrls(search, limit, offset);
  const totalUrls = await urlService.getTotalUrls(search);
  const totalPages = Math.ceil(totalUrls / limit);
  
  res.render('my-links', {
    urls,
    totalUrls,
    totalPages,
    currentPage: parseInt(page),
    search,
    password,
    baseUrl: BASE_URL
  });
});

app.get('/short', (req, res) => {
  const { password } = req.query;
  
  if (!isAuthorized(password)) {
    return res.render('login', { baseUrl: BASE_URL });
  }
  
  res.render('short', { password, baseUrl: BASE_URL, error: null, successUrl: null, shortCode: null });
});

app.post('/short', async (req, res) => {
  const { password, url, custom_alias } = req.body;
  
  if (!isAuthorized(password)) {
    return res.render('login', { baseUrl: BASE_URL });
  }
  
  const result = await urlService.createShortUrl(url, custom_alias || null);
  
  if (result.success) {
    res.render('short', { 
      password, 
      baseUrl: BASE_URL, 
      error: null, 
      successUrl: result.data.short_url,
      shortCode: result.data.short_code
    });
  } else {
    res.render('short', { 
      password, 
      baseUrl: BASE_URL, 
      error: result.error, 
      successUrl: null,
      shortCode: null
    });
  }
});

app.get('/stats', async (req, res) => {
  const { password, code } = req.query;
  
  if (!isAuthorized(password)) {
    return res.render('login', { baseUrl: BASE_URL });
  }
  
  const stats = await urlService.getStats(code);
  
  if (!stats.success) {
    return res.redirect(`/my-links?password=${encodeURIComponent(password)}`);
  }
  
  res.render('stats', { stats: stats.data, password, baseUrl: BASE_URL });
});

app.get('/s/:code', async (req, res) => {
  const { code } = req.params;
  
  const url = await urlService.getUrlByCode(code);
  
  if (!url) {
    return res.status(404).send('URL not found');
  }
  
  if (!url.is_active) {
    return res.status(410).send('This URL has been deactivated');
  }
  
  if (url.expires_at && new Date(url.expires_at) < new Date()) {
    return res.status(410).send('This URL has expired');
  }
  
  const referer = req.get('referer') || null;
  const userAgent = req.get('user-agent') || null;
  const ipAddress = req.ip || req.connection.remoteAddress;
  
  await urlService.incrementClick(url.id);
  await urlService.logClick(url.id, referer, userAgent, ipAddress);
  
  res.redirect(url.original_url);
});

app.post('/api/shorten', async (req, res) => {
  const { url, custom_alias, expires_at } = req.body;
  
  if (!url) {
    return res.status(400).json({ success: false, error: 'URL is required' });
  }
  
  const result = await urlService.createShortUrl(url, custom_alias || null, null, expires_at || null);
  
  if (result.success) {
    res.status(201).json({ success: true, data: result.data });
  } else {
    res.status(400).json({ success: false, error: result.error });
  }
});

app.get('/api/list', async (req, res) => {
  const urls = await urlService.getAllUrls();
  res.json({ success: true, data: urls });
});

app.get('/api/stats/:code', async (req, res) => {
  const { code } = req.params;
  const stats = await urlService.getStats(code);
  
  if (stats.success) {
    res.json(stats);
  } else {
    res.status(404).json(stats);
  }
});

const PORT = process.env.PORT || 3000;

async function start() {
  await connect();
  app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
  });
}

start();
