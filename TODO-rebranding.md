# TODO: Rebranding Color Scheme - Islamic Thinker Theme

## Status Tracking
- ✅ = Selesai dikerjakan
- 🔄 = Sedang dikerjakan
- ⏳ = Belum dikerjakan

---

## 1. SYSTEM COMPONENTS

### Color Palette & Foundation
- ✅ `resources/css/app.css` - Color palette baru (charcoal, beige, gold)

### Navigation Components
- ✅ `resources/views/components/nav-guest.blade.php` - Navbar public dengan gold accent
- ⏳ `resources/views/components/navigation-auth.blade.php` - Navbar authenticated users
- ✅ `resources/views/components/course-card.blade.php` - Card course dengan gold/charcoal

### Footer Components
- ✅ `resources/views/components/simple-footer.blade.php` - DONE: charcoal-900 bg, gold hover, beige text

### Form Components (Optional - Backend)
- ⏳ `resources/views/components/primary-button.blade.php` - Button masih mountain-meadow
- ⏳ `resources/views/components/secondary-button.blade.php`
- ⏳ `resources/views/components/text-input.blade.php`
- ⏳ `resources/views/components/input-label.blade.php`
- ⏳ `resources/views/components/modal.blade.php`

---

## 2. PUBLIC PAGES (PRIORITY)

### Homepage
- ✅ `resources/views/front/index.blade.php` - DONE: Hero, Featured Courses, Values

### Course Catalog
- ✅ `resources/views/front/course-catalog.blade.php` - DONE: Hero with stats, Benefits section

### Course Details Page
- ✅ `resources/views/front/course-details.blade.php` - DONE
  - Hero section: beige-50, gold badges
  - Stats boxes: gold-50/10 backdrop-blur
  - Price box: gold-50/20 backdrop-blur
  - Curriculum: charcoal-900 gradient bg, beige-50 cards
  - All sections fully rebranded

### Course Checkout
- ⏳ `resources/views/front/course-checkout.blade.php`
  - Hero/Header section
  - Checkout form container
  - Summary box colors
  - Button colors

### Checkout Success
- ⏳ `resources/views/front/course-checkout-success.blade.php`
  - Success message container
  - Button colors
  - Background colors

### Course Preview (Learning)
- ⏳ `resources/views/front/course-preview.blade.php`
  - Video player container
  - Sidebar navigation
  - Progress indicators
  - Background colors

### Terms of Service
- ✅ `resources/views/front/terms-of-service.blade.php` - DONE
  - Hero: charcoal-900 gradient, gold badges
  - Content: Alternating white/beige-100 sections
  - All gold accents dan charcoal text
  - Contact section dengan gold backdrop

---

## 3. AUTHENTICATED/DASHBOARD PAGES (OPTIONAL - LOW PRIORITY)

Halaman dashboard/authenticated bisa dikerjakan nanti jika diperlukan, fokus public pages dulu.

---

## 4. DETAILED TASKS PER PAGE

### ✅ DONE: course-details.blade.php
- [x] Hero section: bg-beige-50, gold badges, charcoal text
- [x] Stats boxes: gold-50/10 backdrop-blur
- [x] Price box: gold-50/20 backdrop-blur dengan gold-300 border
- [x] Buttons: gold-600 dengan shadow cinematic
- [x] Main content: charcoal-900 gradient background
- [x] Curriculum cards: beige-50 dengan elevated shadow
- [x] Section headers: gold-600 dengan charcoal-900 text
- [x] Lesson items: hover gold-400 border
- [x] Sidebar: beige-50 cards dengan gold accents
- [x] Share dropdown: beige-50 dengan gold-50 hover
- [x] All text: charcoal palette (800, 700, 600, 500, 400)

### ✅ DONE: simple-footer.blade.php
- [x] bg-charcoal-900 dengan border-charcoal-800
- [x] text-beige-300 untuk text default
- [x] gold-400 hover untuk links
- [x] Social media icons dengan hover:scale-110
- [x] Copyright section dengan beige-400 text

### ✅ DONE: terms-of-service.blade.php
- [x] Hero: charcoal-900 gradient dengan gold badge
- [x] Content sections: Alternating white/beige-100
- [x] All badges: gold-100 dengan gold-600 icons
- [x] All headings: charcoal-800
- [x] All text: charcoal-700
- [x] Contact section: gold-50/20 backdrop-blur
- [x] Links: gold-700 hover gold-900

### ⏳ course-checkout.blade.php
- TBD: Baca file lengkap untuk identifikasi

### ⏳ course-checkout-success.blade.php
- TBD: Baca file lengkap untuk identifikasi

### ⏳ course-preview.blade.php
- TBD: Baca file lengkap untuk identifikasi

---

## 5. COLOR MAPPING GUIDE

### Old → New Mapping
```
BACKGROUNDS:
bg-white → bg-beige-50 (light sections) atau bg-charcoal-900 (dark sections)
bg-gray-50 → bg-beige-100 atau bg-gold-50/10 backdrop-blur
bg-gray-100 → bg-beige-200
hero-gradient → bg-gradient-to-br from-charcoal-800 via-charcoal-700 to-charcoal-800

BORDERS:
border-gray-100 → border-beige-200
border-gray-200 → border-charcoal-800 (dark) atau border-beige-200 (light)
border-mountain-meadow-200 → border-gold-400

TEXT:
text-gray-900 → text-charcoal-800 (light bg) atau text-beige-50 (dark bg)
text-gray-700 → text-charcoal-700 (light bg) atau text-beige-100 (dark bg)
text-gray-600 → text-charcoal-600 (light bg) atau text-beige-300 (dark bg)
text-gray-500 → text-charcoal-500 (light bg) atau text-beige-400 (dark bg)

ACCENTS:
mountain-meadow-600 → gold-600
mountain-meadow-700 → gold-700
mountain-meadow-50 → gold-50/10 backdrop-blur
mountain-meadow-100 → gold-100

BUTTONS:
bg-mountain-meadow-600 → bg-gold-600
hover:bg-mountain-meadow-700 → hover:bg-gold-500
```

### Design Principles
1. **Sections alternation**: Dark (charcoal) → Light (beige) → Dark → Light
2. **Cards**: Glassmorphism dengan border gold-500/10
3. **Hover effects**: Scale, translate, shadow cinematic
4. **Icons**: gold-400 atau gold-600
5. **Badges**: gold-600/10 background dengan border gold-600/20

---

## 6. EXECUTION PLAN

### Phase 1: Critical Public Pages ✅ COMPLETED
1. ✅ index.blade.php
2. ✅ course-catalog.blade.php
3. ✅ course-details.blade.php
4. ✅ simple-footer.blade.php
5. ✅ terms-of-service.blade.php

### Phase 2: Transactional Pages
6. ⏳ course-checkout.blade.php
7. ⏳ course-checkout-success.blade.php

### Phase 3: Learning Pages
8. ⏳ course-preview.blade.php

### Phase 4: Components Polish
9. ⏳ navigation-auth.blade.php
10. ⏳ Form components (if needed for public forms)

---

## 7. TESTING CHECKLIST

Setelah setiap page selesai:
- [ ] Cek responsive (mobile, tablet, desktop)
- [ ] Cek kontras warna (text readability)
- [ ] Cek hover states semua interactive elements
- [ ] Cek glassmorphism/backdrop-blur berfungsi
- [ ] Cek consistency dengan design system
- [ ] Cek tidak ada sisa mountain-meadow atau gray lama

---

**Created**: 2025-01-20
**Last Updated**: 2025-01-20 15:30 WIB
**Completion**: 93% (14/15 major pages) - USER FEEDBACK INCORPORATED! ✅

## SUMMARY PROGRESS - PHASE 1-4 COMPLETED! 🎉

### ✅ PHASE 1: PUBLIC PAGES (7 files)
- ✅ Homepage (index.blade.php)
- ✅ Course Catalog (course-catalog.blade.php)
- ✅ Course Details (course-details.blade.php)
- ✅ Terms of Service (terms-of-service.blade.php)
- ✅ Navbar Guest (nav-guest.blade.php)
- ✅ Course Card (course-card.blade.php)
- ✅ Footer (simple-footer.blade.php)

### ✅ PHASE 2: TRANSACTIONAL (2 files)
- ✅ Course Checkout (course-checkout.blade.php) ⭐
- ✅ Checkout Success (course-checkout-success.blade.php) ⭐

### ✅ PHASE 3: AUTHENTICATED (1 file)
- ✅ Navigation Auth (navigation-auth.blade.php) ⭐

### ✅ PHASE 4: AUTH PAGES (1 file)
- ✅ Login Page (auth/login.blade.php) ⭐

### ✅ USER FEEDBACK FIXES (3 issues) 🎯
- ✅ Course Details Curriculum (lesson items expanded view)
- ✅ Dashboard Join Course Page (`/dashboard/join/[slug]`)
- ✅ Learning Page (major authenticated page)

### ⏳ OPTIONAL REMAINING (3 files - LOW PRIORITY)
- ⏳ Register (same pattern as login - 5 min work)
- ⏳ Dashboard Home (form-intensive - optional)
- ⏳ Profile Edit (form-intensive - optional)

## DESIGN THEME: FULL DARK ✅
**RULE:** SEMUA background WAJIB GELAP (charcoal-900/800)
**TEXT:** Text terang (beige-50/300) pada bg gelap
**ACCENTS:** Gold (400/600) untuk links dan CTAs
**STATUS:** ✅ PHASE 2,3,4 COMPLETED - 11 MAJOR PAGES DARK!
