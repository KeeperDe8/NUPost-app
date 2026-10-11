# 🚀 NUPost Web Performance & Smoothness Guide
> A practical developer guide on resolving git conflicts, eliminating page reloads (YouTube/Facebook-style), implementing skeleton loaders, and applying GPU-accelerated animations in Laravel Blade.

---

## 0. ⚠️ URGENT: Fix Git Conflict Markers in `analytics.blade.php`

Before making UI improvements, there is a critical syntax bug committed to the repository that will crash the Admin Analytics/Visualization page:

* **File:** `resources/views/admin/analytics.blade.php`
* **Issue:** Merge conflict markers were accidentally pushed around lines **6–8**, **88–89**, **94–97**, **411–436**, **617–625**, **870–878**, and **1000–1088**.
* **How to fix:**
  1. Open `resources/views/admin/analytics.blade.php`.
  2. Search for `<<<<<<<` throughout the file.
  3. Keep the **new search bar feature** and remove the conflict markers:
     * In CSS: keep `.vis-search-wrap`, `.vis-search-input`, etc.
     * In HTML (around line 415): keep `<div class="vis-search-wrap" id="vis-search-wrap">...</div>`.
     * In tables (around lines 620 & 874): keep `<table class="rtable" id="vis-recent-table">` and `<table class="rtable" id="vis-fb-table">`.
     * In JavaScript (around line 1003): keep the `applyVisSearch()` function.
     * Delete all lines containing `<<<<<<< HEAD`, `=======`, and `>>>>>>> ...`.

---

## 1. Why does every page change cause a full reload?

### The Traditional Web (Multi-Page Application)
Currently, clicking regular links (`<a href="/profile">`) triggers a standard browser reload:
1. **Unloads everything:** Destroys the current DOM, stops JavaScript, and clears the screen (causing the white reload flicker).
2. **Re-downloads assets:** Downloads new HTML and re-parses CSS stylesheets, web fonts, and JS bundles from scratch.
3. **Rebuilds layout:** Redraws the header, sidebar, navigation, and page content every single click.

### Why YouTube and Facebook DON'T reload:
* When navigating, the **browser never unloads or reloads**.
* Top headers, sidebars, and background media players stay permanently alive in memory.
* JavaScript intercepts the link clicks, fetches **only the changing content** via background AJAX/Fetch, swaps out the `<main>` container, and updates the URL using `window.history.pushState()`.

---

## 2. Zero-Reload Navigation (Drop-in Setup)

We do **NOT** need to rewrite the project into React or Vue. We can achieve this in Laravel Blade with two drop-in libraries:

### Step 1: Add Hotwired Turbo Drive
In both **`resources/views/layouts/admin.blade.php`** and **`resources/views/layouts/requestor.blade.php`**, add this inside the `<head>`:

```html
<!-- Turbo Drive: Replaces page reloads with seamless background swaps -->
<script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.es2017-umd.js"></script>
```

### Step 2: Add Link Pre-fetching (`instant.page`)
Users hover over a link for **200ms to 300ms** before clicking. `instant.page` pre-fetches the next page during that hover so navigation feels instant (0ms).

Add this single line right before `</body>` in both layout files:
```html
<script src="//instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0Rck6V38m2BQHTEXv80QwJuJChhxLMgkE4g++aD4IVFzpBc1z"></script>
```

### Step 3: JavaScript Compatibility Rule with Turbo
With Turbo active, pages do not reload from scratch. If you have page-specific scripts that use `DOMContentLoaded`, update them to listen for `turbo:load`:

```javascript
// Change this:
document.addEventListener('DOMContentLoaded', function() { ... });

// To this (works for both initial load and Turbo transitions):
document.addEventListener('turbo:load', function() { ... });
```

---

## 3. Drop-in Skeleton Loader System

Instead of a blank screen or a spinning wheel while data loads, render gray skeleton placeholder blocks with a modern shimmer animation.

### Global CSS (Add to `admin.blade.php` & `requestor.blade.php` `<style>`):

```css
/* ── Reusable Skeleton Shimmer System ────────────────────────────── */
@keyframes skeleton-shimmer {
    0%   { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.skeleton {
    background: linear-gradient(
        90deg,
        rgba(0, 0, 0, 0.06) 25%,
        rgba(0, 0, 0, 0.12) 37%,
        rgba(0, 0, 0, 0.06) 63%
    );
    background-size: 400% 100%;
    animation: skeleton-shimmer 1.5s ease-in-out infinite;
    border-radius: 8px;
    display: inline-block;
}

/* Dark mode support */
[data-theme="dark"] .skeleton {
    background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0.05) 25%,
        rgba(255, 255, 255, 0.12) 37%,
        rgba(255, 255, 255, 0.05) 63%
    );
    background-size: 400% 100%;
}

/* Common Skeleton Helpers */
.skeleton-text       { height: 14px; width: 80%; border-radius: 4px; margin-bottom: 6px; }
.skeleton-text-sm    { height: 11px; width: 50%; border-radius: 4px; }
.skeleton-title      { height: 22px; width: 60%; border-radius: 6px; margin-bottom: 10px; }
.skeleton-badge      { height: 22px; width: 68px; border-radius: 99px; }
.skeleton-avatar     { width: 42px; height: 42px; border-radius: 50%; }
.skeleton-card       { width: 100%; height: 110px; border-radius: 16px; margin-bottom: 14px; }
```

### HTML Examples (How to use it in Blade templates):

#### Example A: Skeleton Stat Card Placeholder
```html
<div class="scard">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div class="skeleton" style="width:36px; height:36px; border-radius:10px;"></div>
        <div class="skeleton skeleton-badge"></div>
    </div>
    <div class="skeleton skeleton-title" style="width:40%;"></div>
    <div class="skeleton skeleton-text-sm"></div>
</div>
```

#### Example B: Skeleton Table Row Placeholder
```html
<tr class="skeleton-row">
    <td><div class="skeleton skeleton-text" style="width:120px;"></div></td>
    <td><div class="skeleton skeleton-text-sm" style="width:90px;"></div></td>
    <td><div class="skeleton skeleton-badge"></div></td>
    <td><div class="skeleton skeleton-text-sm" style="width:70px;"></div></td>
</tr>
```

---

## 4. Smooth GPU Micro-Animations & Page Fades

### Rule 1: Only animate `transform` and `opacity`
* ❌ **DO NOT animate:** `top`, `left`, `width`, `height`, `margin`, `padding` (these trigger CPU layout reflows and stutter).
* ✅ **DO animate:** `transform: translateY(-3px)`, `transform: scale(1.02)`, `opacity: 0.8` (handled directly by the GPU).

### Rule 2: Smooth Page Entrance Animation
Add this to the global stylesheet so every page smoothly fades in when navigated to via Turbo:

```css
@keyframes pageFadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

main, .main-content, .content-wrap {
    animation: pageFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
```

### Rule 3: Keep animation timings snappy
* Hover animations: `150ms – 200ms`.
* Modal popups: `200ms – 250ms`.
* Anything over **300ms** feels sluggish to everyday users.

---

## 5. Production Server Speed Checklist (Hostinger)

Run these commands in the server terminal or SSH to ensure Laravel caches everything in memory:

```bash
# 1. Cache configuration, routes, and compiled views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Optimize Composer autoloader
composer install --optimize-autoloader --no-dev
```

### Database Eager Loading:
Ensure database queries avoid the N+1 problem by using eager loading:
```php
// Good: 2 fast queries
$requests = PostRequest::with('user')->latest()->get();

// Bad: 100+ separate queries inside Blade loops
$requests = PostRequest::latest()->get();
```
