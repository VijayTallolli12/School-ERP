# Mobile Branding System Report

## Release 1

---

## Files Modified

### Laravel Backend (API)

| File | Action | Description |
|------|--------|-------------|
| `routes/modules/api.php` | Modified | Registered public branding route |
| `routes/modules/api/branding.php` | Created | Branding API route definition |
| `app/Http/Controllers/Api/V1/BrandingController.php` | Created | Branding endpoint controller |

### Mobile App (Expo/React Native)

| File | Action | Description |
|------|--------|-------------|
| `mobile/app.json` | Created | Expo config with default build-time branding |
| `mobile/package.json` | Created | Mobile app dependencies |
| `mobile/src/App.tsx` | Created | App entry point with NavigationContainer |
| `mobile/src/branding/BrandingService.ts` | Created | Branding fetch, cache, and refresh logic |
| `mobile/src/branding/BrandingContext.tsx` | Created | React context provider for branding/theme |
| `mobile/src/branding/theme.ts` | Created | Theme builder, color normalizer, sanitizer |
| `mobile/src/branding/fallback.ts` | Created | Default branding and theme constants |
| `mobile/src/screens/LoginScreen.tsx` | Created | Branded login screen |
| `mobile/src/screens/SplashScreen.tsx` | Created | Branded splash screen |
| `mobile/src/screens/WelcomeScreen.tsx` | Created | Branded welcome screen |
| `mobile/src/screens/ProfileScreen.tsx` | Created | Branded profile screen |
| `mobile/src/screens/AboutScreen.tsx` | Created | Branded about screen |
| `mobile/src/components/Header.tsx` | Created | Branded header component |
| `mobile/src/components/DrawerHeader.tsx` | Created | Branded drawer header component |
| `mobile/src/components/LoadingScreen.tsx` | Created | Branded loading screen component |
| `mobile/src/components/EmptyState.tsx` | Created | Branded empty state component |
| `mobile/src/navigation/AppNavigator.tsx` | Created | Stack + drawer navigation setup |

### Super Admin Configuration (ERP Admin Panel)

| File | Action | Description |
|------|--------|-------------|
| `resources/views/layouts/partials/sidebar.blade.php` | Modified | Added "Mobile Branding" link under Administration (settings.view permission) |
| `routes/modules/settings.php` | Modified | Added mobile branding GET/POST routes |
| `app/Modules/Settings/Controllers/SettingsController.php` | Modified | Added `mobileBranding()` and `updateMobileBranding()` methods |
| `app/Modules/Settings/Services/SettingsService.php` | Modified | Added `updateBranding()` and `branding()` methods |
| `app/Modules/Settings/Requests/UpdateMobileBrandingRequest.php` | Created | Brand color validation |
| `resources/views/modules/settings/mobile-branding.blade.php` | Created | Mobile branding configuration page |

---

## Super Admin Access

### Sidebar Navigation

Super admins can access the mobile branding configuration from the admin sidebar:

```
Administration
  ├── Settings            → /admin/settings
  └── Mobile Branding     → /admin/settings/mobile/branding
```

### Routes

| Route | Method | Permission | Description |
|-------|--------|------------|-------------|
| `/admin/settings/mobile/branding` | GET | `settings.view` | Mobile branding configuration page |
| `/admin/settings/mobile/branding` | POST | `settings.update` | Save brand colors |

### What the Super Admin Can Configure

The Mobile Branding page provides:

1. **Brand Colors** — Primary and secondary colors (hex input with color picker). These are served to all four mobile apps (Parent, Student, Teacher, Driver) through the branding API.
2. **Mobile App Preview** — A live phone mockup showing how the splash/login screens will look with the configured colors and school logo.
3. **Branding API Info** — The endpoint URL and supported headers/params, with copy-to-clipboard and open-in-new-tab buttons.

### Configuration Chain

```
Super Admin configures colors
    ↓
Saved to school.settings.brand.primary_color / secondary_color
    ↓
Mobile apps fetch GET /api/v1/branding?school_id=X
    ↓
BrandingController reads brand colors (falls back to #2563eb / #64748b)
    ↓
Mobile app applies colors (falls back to defaults if API unreachable)
```

---

## Branding Source

### Runtime Branding (ERP)

Branding is fetched from the School ERP at runtime via the `/api/v1/branding` endpoint.

**Endpoint:** `GET /api/v1/branding`

**Authentication:** None (public endpoint)

**School Identification:**
- `X-School-Id` HTTP header
- `school_id` query parameter

**Data Source (ERP Settings):**

| Branding Field | ERP Source | Fallback |
|---------------|-----------|----------|
| `school_name` | `setting('school_name')` → `School.name` | `config('app.name', 'School ERP')` |
| `school_logo` | `setting('school_logo')` → `School.logo_path` | `null` (mobile uses default) |
| `favicon` | `setting('favicon')` → `settings.school.favicon_path` | `null` |
| `primary_color` | `school.settings.brand.primary_color` | `#2563eb` |
| `secondary_color` | `school.settings.brand.secondary_color` | `#64748b` |
| `school_website` | `setting('website')` | Empty string |
| `school_address` | `setting('address')` | Empty string |
| `school_phone` | `setting('phone')` | Empty string |
| `app_name` | `config('app.name', 'School ERP')` | `School ERP` |

### Build-Time Branding (Default)

Build-time branding is defined in `mobile/app.json` and is **not** dynamically changed at runtime:

| Field | Default Value |
|-------|--------------|
| App Name | School ERP |
| Package Name | com.schoolerp.app |
| Icon | `./assets/icon.png` |
| Splash | `./assets/splash.png` |
| Splash Background Color | `#2563eb` |

---

## Fallback Logic

The branding system implements a multi-layer fallback chain:

### 1. API Response Fallback

```
ERP Branding Available → Use ERP values
ERP Branding Missing   → Use defaults from BrandingController
```

The `BrandingController` returns default values when:
- No school ID is provided (`X-School-Id` header / `school_id` param)
- The school is not found in the database
- The ERP settings are incomplete

### 2. Mobile App Fallback

The mobile app applies additional fallbacks via `sanitizeBranding()` in `theme.ts`:

```
API returns valid data → Use API data
API returns null/empty → Use sanitizeBranding() defaults
API unreachable        → Use cached branding (if fresh)
No cache available     → Use DEFAULT_BRANDING constants
```

### 3. Specific Fallbacks

| Scenario | Behavior |
|----------|----------|
| Logo missing from ERP | `school_logo` is `null` → mobile shows initial letter placeholder |
| School name missing | Falls back to `config('app.name', 'School ERP')` |
| Primary color invalid/missing | Falls back to `#2563eb` (default blue) |
| Secondary color invalid/missing | Falls back to `#64748b` (default gray) |
| API completely unreachable | Uses cached branding or `DEFAULT_BRANDING` |
| First install (no cache) | Uses `DEFAULT_BRANDING` |

### 4. Never Display Broken Images

- The mobile app never renders an `<Image>` with a null/empty `uri`
- When `school_logo` is null, a styled placeholder with the school's initial letter is shown instead
- The `normalizeColor()` function validates color strings before applying them

---

## Cache Strategy

### Client-Side Caching (Mobile App)

| Aspect | Detail |
|--------|--------|
| Storage | `@react-native-async-storage/async-storage` |
| Cache Key | `@school_erp_branding` |
| TTL | 30 minutes |
| Cache On | Login, App Restart |
| Cache Refresh | Pull to Refresh, Settings Refresh, Explicit `refreshBranding()` call |

### Cache Flow

```
1. App starts → Check cache (if valid, use cached branding)
2. Cache expired or missing → Fetch from ERP API
3. Fetch succeeds → Cache response, apply branding
4. Fetch fails → Use cached branding if available, else DEFAULT_BRANDING
5. User pulls to refresh → Clear cache, fetch fresh from ERP
6. User logs in → Fetch fresh branding for the school
```

### Server-Side Caching

No server-side caching is implemented for the branding endpoint. The endpoint reads directly from the database on each request, ensuring branding changes are reflected immediately.

---

## Verification

### Scenario 1: ERP Branding Configured

- **Given:** A school has branding configured in the ERP (logo, name, colors, etc.)
- **When:** The mobile app fetches branding via `/api/v1/branding?school_id=1`
- **Then:** The app displays the school's custom branding on all screens

### Scenario 2: ERP Branding Missing

- **Given:** A school has no branding configured (no logo, no colors)
- **When:** The mobile app fetches branding
- **Then:** The app falls back to default School ERP branding (blue theme, initial letter placeholder)

### Scenario 3: Offline Mode

- **Given:** The device has no internet connection
- **When:** The app starts
- **Then:** The app uses cached branding if available; otherwise uses `DEFAULT_BRANDING`
- **No broken images or blank screens are displayed**

### Scenario 4: First Install

- **Given:** The app is installed for the first time with no cached data
- **When:** The app starts
- **Then:** The app fetches branding from the ERP; if unavailable, uses `DEFAULT_BRANDING`

### Scenario 5: Cache Refresh

- **Given:** The app has cached branding
- **When:** The user pulls to refresh or triggers a settings refresh
- **Then:** The cache is cleared and fresh branding is fetched from the ERP

### Scenario 6: Logo Fallback

- **Given:** The ERP has no school logo (`school_logo` is null)
- **When:** The app renders the header, splash, or drawer
- **Then:** A styled placeholder with the school's initial letter is displayed (no broken image)

### Scenario 7: App Name Fallback

- **Given:** The ERP has no school name configured
- **When:** The app displays the app name
- **Then:** "School ERP" is displayed (from `config('app.name')`)

---

## API Endpoint Details

### GET /api/v1/branding

**Headers:**
- `X-School-Id` (optional): School identifier

**Query Parameters:**
- `school_id` (optional): School identifier

**Success Response (200):**
```json
{
  "success": true,
  "message": "Branding retrieved.",
  "data": {
    "school_name": "Example School",
    "school_logo": "https://example.com/storage/logo.png",
    "favicon": "https://example.com/storage/favicon.ico",
    "primary_color": "#2563eb",
    "secondary_color": "#64748b",
    "school_website": "https://example.com",
    "school_address": "123 Main St",
    "school_phone": "+1-555-0123",
    "app_name": "School ERP"
  }
}
```

**Default Response (when school not found or no ID provided):**
```json
{
  "success": true,
  "message": "Default branding returned.",
  "data": {
    "school_name": "School ERP",
    "school_logo": null,
    "favicon": null,
    "primary_color": "#2563eb",
    "secondary_color": "#64748b",
    "school_website": "",
    "school_address": "",
    "school_phone": "",
    "app_name": "School ERP"
  }
}
```

---

## Design Decisions

1. **No duplicate APIs:** The branding endpoint reuses the existing `setting()` helper and `School` model. No new settings table or model was created.

2. **No white-label builds:** Launcher icons, package names, and application names remain default. Only runtime branding (colors, logo, name) is dynamic.

3. **No hardcoded branding:** All branding values are fetched from the ERP at runtime with proper fallbacks.

4. **Cache TTL of 30 minutes:** Balances freshness with performance and offline capability.

5. **Public branding endpoint:** No authentication required so mobile apps can fetch branding before login (needed for splash, login, and welcome screens).

6. **Color normalization:** Invalid color values from the ERP are silently replaced with defaults, preventing UI breakage.

---

## Stop

Implementation complete. No white-label builds, no dynamic launcher icons, no package name changes.
