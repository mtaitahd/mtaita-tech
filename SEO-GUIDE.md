# Mtaita Tech — SEO & Google Search Console Guide

## 1. Google Search Console Setup

1. Go to https://search.google.com/search-console
2. Sign in with a Google account (e.g. mtaitajohnson7@gmail.com)
3. Add property → type: **URL prefix**
4. Enter: `https://mtaita-tech.com` (or your live domain)
5. Verify ownership using **HTML tag** method:
   - Copy the meta tag Google provides
   - Paste it in `header.php` inside the `<head>` section (after line 14)
   - Upload and click **Verify**

## 2. Submit Sitemap

1. In Search Console, go to **Sitemaps** (left sidebar)
2. Enter: `sitemap.xml`
3. Click Submit
4. The dynamic sitemap (`sitemap.php`) auto-generates:
   - Static pages: Home, Portfolio, Contact, Blog
   - Dynamic entries: every blog post (query from `blogs` table)
   - Each blog post URL: `https://mtaita-tech.com/blog/{slug}`

## 3. Request Indexing

### All pages to submit:

| Page | URL | Priority |
|---|---|---|
| Home | `https://mtaita-tech.com/` | Highest |
| Portfolio | `https://mtaita-tech.com/portfolio` | High |
| Blog | `https://mtaita-tech.com/blog` | High |
| Contact | `https://mtaita-tech.com/contact` | Medium |
| About | `https://mtaita-tech.com/about` | Medium |
| Services | `https://mtaita-tech.com/services` | Medium |
| Each blog post | `https://mtaita-tech.com/blog/{slug}` | High |

### How to request indexing:
1. In Search Console, paste each URL in the **URL inspection** bar (top)
2. Click **Request Indexing**
3. Do this for all pages listed above
4. New blog posts → request indexing after publishing

## 4. robots.txt (already configured)

```
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /.env
Disallow: /config.php
Disallow: /db_connect.php
Disallow: /mailer.php
Disallow: /smtp.txt

Sitemap: http://localhost/mtaita-tech/sitemap.xml
```
**Before going live**: update the sitemap URL in `robots.txt` to your production domain:
```
Sitemap: https://mtaita-tech.com/sitemap.xml
```

## 5. SEO tags per page (already implemented)

| Tag | Location | Notes |
|---|---|---|
| `<title>` | `header.php:35` | Set via `$page_title` |
| `<meta name="description">` | `header.php:16` | Set via `$page_desc` |
| `<meta name="keywords">` | `header.php:17` | Set via `$page_keywords` |
| `<meta name="author">` | `header.php:18` | Static: "Mtaita Tech" |
| `<link rel="canonical">` | `header.php:33` | Auto from `$_SERVER['REQUEST_URI']` |
| `<meta property="og:*">` | `header.php:19-23` | Open Graph for social sharing |
| `<meta name="twitter:card">` | `header.php:32` | Twitter card |
| `<script type="application/ld+json">` (Article) | `header.php:37-55` | Article schema on blog posts |
| `<script type="application/ld+json">` (BreadcrumbList) | `blog.php`, `single-blog.php` | Breadcrumbs for navigation |

## 6. SEO keywords (from seo.txt)

Use these naturally in page content, headings, and meta tags:

**Core:** custom software development, enterprise software solutions, mobile app development, cloud migration consulting, IT infrastructure management, tech support

**Commercial/Hire:** hire custom software developers, affordable IT consulting for small business, scalable cloud architecture services, software engineering company for startups

**Local:** software development company in Tanzania, IT consulting services Dar es Salaam, tech support solutions East Africa

**Blog topics:** how to plan cloud migration without downtime, why my business needs custom software vs SaaS, how to secure mobile app user data

## 7. Before going live checklist

- [ ] Update `SITE_URL` in `.env` to production URL
- [ ] Update `robots.txt` sitemap URL to production domain
- [ ] Remove `http://localhost` references from all hardcoded URLs in PHP files
- [ ] Verify all internal links use the correct base path
- [ ] Test sitemap: visit `https://yoursite.com/sitemap.xml` in browser
- [ ] Test robots.txt: visit `https://yoursite.com/robots.txt`
- [ ] Confirm no pages return 500 errors
- [ ] Submit sitemap to Google Search Console
- [ ] Request manual indexing for key pages
- [ ] Test with PageSpeed Insights / Lighthouse
- [ ] Verify mobile responsiveness

## 8. Monitoring

- **Google Search Console**: check for crawl errors, index coverage, performance
- **Analytics**: connect Google Analytics for traffic data
- **Regular blog posts**: new content helps indexing and ranking
- **Check PHP error log**: `C:\xampp\apache\logs\error.log`
