# OfRoot Frontend Brand & UI Design Guide

> This guide is a living document for product design, marketing pages, and admin UI. Narrative first, code snippets second, so it reads as a handbook yet stays directly actionable.

---

## 1) Brand narrative and tone

- Position: Pragmatic, senior engineering partner. We ship secure, production-grade SaaS quickly.
- Voice: Clear, concise, direct. Prefer active voice. Avoid jargon; show proof.
- Personality: Calm confidence, high signal, humble expertise.

Use this to inform headings, CTAs, and microcopy. Examples:
- Headline: “Ship secure SaaS faster.”
- CTA: “Start free” (public), “Continue to checkout” (paid).
- Empty states: “No users yet. Invite your team to get started.”

---

## 2) Core visual identity

- Logo: Circular avatar found in `/public/ofroot-logo.png`. Keep generous whitespace around it (≥ logo height).
- Color system: Brand teal with neutral grays and purposeful accents.

Primary brand (teal)
- Base: `#20B2AA` (LightSeaGreen)
- Use for primary actions, focus states, and highlights.

Supporting accents
- Success: Emerald (`green-600`/`#059669`)
- Warn/Attention: Amber (`amber-600`/`#D97706`)
- Danger: Rose (`rose-600`/`#E11D48`)
- Info: Sky (`sky-600`/`#0284C7`)

Neutrals
- Use Tailwind `gray` family with accessible contrast (500–900 for text).

---

## 3) Design tokens (semantic, Tailwind-oriented)

Use semantic tokens in components; map them to Tailwind utilities. When needed, expose CSS variables for themeing (dark-mode, brand swaps).

CSS variables (drop into `app/globals.css` if desired):
```css
:root {
  --color-brand-50:  #E6FAF7;
  --color-brand-100: #CFF5F0;
  --color-brand-200: #A0EBE0;
  --color-brand-300: #72E1D0;
  --color-brand-400: #44D7C1;
  --color-brand-500: #20B2AA; /* primary */
  --color-brand-600: #1B8F8A;
  --color-brand-700: #166F6B;
  --color-brand-800: #104E4C;
  --color-brand-900: #0B3333;

  --radius-card: 12px;
  --radius-pill: 9999px;
  --shadow-card: 0 1px 2px rgba(0,0,0,0.06), 0 2px 8px rgba(0,0,0,0.04);
}
```

Tailwind config extension (reference):
```ts
// tailwind.config.ts (reference snippet)
import type { Config } from 'tailwindcss';
export default {
  theme: {
    extend: {
      colors: {
        brand: {
          50:'#E6FAF7',100:'#CFF5F0',200:'#A0EBE0',300:'#72E1D0',400:'#44D7C1',
          500:'#20B2AA',600:'#1B8F8A',700:'#166F6B',800:'#104E4C',900:'#0B3333',
        },
      },
      borderRadius: {
        xl: '12px',
        '2xl': '16px',
        pill: '9999px',
      },
      boxShadow: {
        card: 'var(--shadow-card)',
      },
      fontFamily: {
        // Prefer system or Inter if available
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'Apple Color Emoji', 'Segoe UI Emoji'],
      },
    },
  },
} satisfies Config;
```

Semantic usage cheatsheet:
- Primary action: `bg-brand-600 hover:bg-brand-700 text-white`
- Subtle link: `text-brand-700 hover:text-brand-800 underline-offset-2`
- Focus ring: `focus:outline-none focus:ring-2 focus:ring-brand-500`
- Card: `rounded-xl border bg-white/80 backdrop-blur shadow-card`

---

## 4) Typography and rhythm

- Base size: 16px, scale using Tailwind (`text-sm`, `text-base`, `text-lg`, `text-xl`, `text-2xl`, …).
- Headings: Tight tracking for large display text; use gradient when hero emphasis is desired.
- Body: 1.5 line-height for readability. Keep line-length ≈ 60–80 characters.

Examples:
```jsx
<h1 className="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-tight">
  <span className="bg-gradient-to-b from-black to-gray-700 bg-clip-text text-transparent">Start your subscription</span>
</h1>
<p className="mt-3 text-base sm:text-lg text-gray-600 max-w-2xl">Concise value statement.</p>
```

---

## 5) Layout, spacing, and elevation

- Page container: `max-w-6xl mx-auto px-4 md:px-6`.
- Vertical rhythm: `py-12 md:py-16` for major sections.
- Cards: `rounded-xl border bg-white/80 backdrop-blur shadow-card`.
- Sticky asides: `md:sticky md:top-24` to keep value props visible.

---

## 6) Components (canonical recipes)

Buttons
```jsx
<button className="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm
  bg-brand-600 text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
  Primary
</button>
<button className="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm
  border text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500">
  Secondary
</button>
```

Badges
```jsx
<span className="inline-flex items-center rounded-full bg-black text-white px-2 py-0.5 text-[10px] leading-4">Most popular</span>
<span className="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] leading-4 text-amber-700 border-amber-200 bg-amber-50">Recommended</span>
```

Cards
```jsx
<div className="rounded-xl border bg-white/80 backdrop-blur p-5 shadow-card">Content</div>
```

Forms
```jsx
<input className="w-full rounded-md border px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500" />
<label className="inline-flex items-center gap-2 text-sm"><input type="checkbox" /> Label</label>
```

Tables
```jsx
<table className="w-full text-sm">
  <thead className="text-left text-gray-500 border-b"><tr><th className="py-2">Name</th></tr></thead>
  <tbody><tr className="border-b hover:bg-gray-50"><td className="py-2">Row</td></tr></tbody>
</table>
```

Modals
```jsx
<div role="dialog" aria-modal className="w-full max-w-lg rounded-lg border bg-white shadow-lg" />
```

---

## 7) Accessibility (non-negotiable)

- Always label controls (`aria-label`, `<label for>`). Use `aria-live` for status.
- Manage focus for dialogs and menus; trap focus when open; restore on close.
- Maintain 4.5:1 contrast for body text; test dark text on light backgrounds.
- Keyboard first: all interactive elements reachable and operable via keyboard.

---

## 8) Motion and feel

- Use small, purposeful transitions: `transition-colors`, `transform-gpu`, subtle `hover:scale-[1.02]` for CTAs.
- Avoid parallax and heavy motion in app surfaces; prefer stability in admin.

---

## 9) Content patterns

- CTAs: one primary per surface. Secondary as plain or bordered buttons.
- Trust: proof bullets over marketing fluff. Show metrics and real screenshots.
- Error copy: human, actionable. E.g., “Subscription failed. Try again or contact support.”

---

## 10) Page archetypes

- Marketing/Subscribe: Hero, plan selector, form, sticky benefits aside.
- Auth (login/register): Minimal, with PublicNavbar and generous white space.
- Admin: Sidebar + toolbar + main content. Use SSR guards; honest zero states.

---

## 11) Implementation checklist

- Primary actions use brand color and accessible focus rings.
- Zero states show truthful empties; never dummy rows.
- Forms validate on blur and submit; errors announced in live regions.
- Dark mode (optional later): map CSS variables to dark palette.

---

## 12) Appendix: Example plan card (as shipped)

```jsx
<label className="relative cursor-pointer text-left rounded-lg border p-4 block overflow-hidden
  transition-colors hover:bg-gray-50">
  {/* Optional centered badge */}
  <div className="flex justify-center mb-2">
    <span className="inline-flex items-center rounded-full bg-black text-white px-2 py-0.5 text-[9px] leading-4">Most popular</span>
  </div>
  <div className="flex items-center justify-between">
    <div className="font-medium">Pro</div>
  </div>
  <div className="mt-1 text-2xl font-semibold">$19<span className="text-sm text-gray-500">/mo</span></div>
  <ul className="mt-2 text-sm text-gray-600 space-y-1">
    <li>• Unlimited projects</li>
    <li>• Priority support</li>
    <li>• Advanced analytics</li>
  </ul>
</label>
```

---

Maintain this guide alongside code changes. When adding new components or pages, quote the relevant section here and link to concrete examples in `app/` or `components/`.
