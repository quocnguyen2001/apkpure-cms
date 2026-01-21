# Botble CMS Design Guidelines

## Overview

This document establishes design standards for Botble CMS admin interfaces based on Tabler UI framework (Bootstrap 5.3.7).

---

## Design System

### Colors

**Primary Colors:**
- Primary: `blue` (#206bc4)
- Secondary: `gray` (#626976)
- Success: `green` (#2fb344)
- Warning: `yellow` (#f76707)
- Danger: `red` (#d63939)
- Info: `azure` (#4299e1)

**Extended Palette:**
- Azure, Indigo, Purple, Pink, Orange, Lime, Teal, Cyan

**Usage:**
- Use `-lt` suffix for light backgrounds: `bg-blue-lt`, `bg-green-lt`
- Use `text-{color}-fg` for foreground text on solid backgrounds

### Status Indicators

**Badges (Tabler Reference):**
```html
<!-- Default -->
<span class="badge bg-green">Active</span>
<span class="badge bg-red">Inactive</span>

<!-- Light variant (recommended for softer appearance) -->
<span class="badge bg-green-lt">Active</span>
<span class="badge bg-red-lt">Inactive</span>

<!-- With icons -->
<span class="badge bg-green d-inline-flex align-items-center gap-1">
    <x-core::icon name="ti ti-check" size="sm" />
    Active
</span>

<!-- Pill variant -->
<span class="badge badge-pill bg-blue">12</span>
```

**Ribbons:**
```html
<div class="ribbon bg-red">Child Theme</div>
<div class="ribbon ribbon-top bg-green">Active</div>
<div class="ribbon ribbon-bookmark bg-blue">Featured</div>
```

### Typography

**Font Scale:**
- Page title: `h2.page-title` (1.5rem, font-weight 600)
- Card title: `h4.card-title` (1rem, font-weight 500)
- Body: 0.875rem
- Small/Caption: 0.75rem

**Utility Classes:**
- `text-truncate` - Single line ellipsis
- `text-secondary` - Muted text
- `fw-bold` - Bold weight

### Spacing

**Standard Spacing Scale:**
- `g-1`: 0.25rem
- `g-2`: 0.5rem
- `g-3`: 1rem
- `g-4`: 1.5rem
- `g-5`: 3rem

**Component Spacing:**
- Card margin-bottom: `mb-4` or `mb-5`
- Button groups: `btn-list` with automatic spacing
- Row gutters: `row-cards` for card grids

---

## Card Components

### Standard Card Structure

```blade
<x-core::card>
    <x-core::card.header>
        <div class="d-flex align-items-center">
            <span class="avatar avatar-sm bg-primary-lt me-3">
                <x-core::icon name="ti ti-icon-name" class="text-primary" />
            </span>
            <div>
                <h3 class="card-title mb-0">Title</h3>
                <p class="text-muted small mb-0">Subtitle</p>
            </div>
        </div>
    </x-core::card.header>
    <x-core::card.body>
        <!-- Content -->
    </x-core::card.body>
    <x-core::card.footer>
        <!-- Actions -->
    </x-core::card.footer>
</x-core::card>
```

### Card with Image

```blade
<x-core::card>
    <div class="img-responsive img-responsive-4x3 card-img-top border-bottom"
         style="background-image: url('{{ $imageUrl }}')">
    </div>
    <x-core::card.body>
        <h4 class="card-title">Title</h4>
    </x-core::card.body>
</x-core::card>
```

### Card Variants

- `card-stacked` - Stacked appearance with shadow
- `card-sm` - Smaller padding
- `card-md` - Medium padding (default)
- `card-lg` - Larger padding

---

## Button Patterns

### Button Component

```blade
<x-core::button
    type="button"
    color="primary|secondary|success|warning|danger|info"
    :outlined="false"
    :ghost="false"
    :disabled="false"
    icon="ti ti-icon-name"
    :iconOnly="false"
    size="sm|md|lg"
>
    Button Text
</x-core::button>
```

### Action Button Groups

```html
<div class="btn-list">
    <x-core::button color="primary" icon="ti ti-check">Primary Action</x-core::button>
    <x-core::button icon="ti ti-trash">Secondary Action</x-core::button>
</div>
```

### Ghost Buttons

For less prominent actions:
```blade
<x-core::button :ghost="true" color="secondary" icon="ti ti-settings">Settings</x-core::button>
```

---

## Grid System

### Card Grid Layout

```blade
<div class="row row-cards mb-5">
    @foreach ($items as $item)
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <x-core::card>...</x-core::card>
        </div>
    @endforeach
</div>
```

### Responsive Breakpoints

- Mobile: `col-12`
- Tablet: `col-sm-6`
- Desktop: `col-lg-4`
- Large Desktop: `col-xl-3`

---

## Empty States

```blade
<div class="empty">
    <div class="empty-icon">
        <x-core::icon name="ti ti-box" />
    </div>
    <p class="empty-title">No items found</p>
    <p class="empty-subtitle text-secondary">
        Description text explaining what to do.
    </p>
    <div class="empty-action">
        <x-core::button color="primary" icon="ti ti-plus">Add Item</x-core::button>
    </div>
</div>
```

---

## Page Header Pattern

```blade
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle">Section Name</div>
            <h2 class="page-title">Page Title</h2>
        </div>
        <div class="col-auto">
            <!-- Header actions -->
        </div>
    </div>
</div>
```

---

## Modals

### Action Modal

```blade
<x-core::modal.action
    id="modal-id"
    type="danger|warning|info|success"
    :title="trans('...')"
    :description="trans('...')"
    :submit-button-label="trans('...')"
/>
```

---

## Icons

**Icon Library:** Tabler Icons (ti ti-*)

**Common Icons:**
- Add: `ti ti-plus`
- Edit: `ti ti-edit`
- Delete: `ti ti-trash`
- Settings: `ti ti-settings`
- Check: `ti ti-check`
- Close: `ti ti-x`
- Search: `ti ti-search`
- Filter: `ti ti-filter`
- Info: `ti ti-info-circle`
- Warning: `ti ti-alert-triangle`
- Success: `ti ti-circle-check`
- Eye/View: `ti ti-eye`
- External Link: `ti ti-external-link`
- Palette/Theme: `ti ti-palette`
- Layout: `ti ti-layout`
- Grid: `ti ti-grid-dots`

---

## Animation & Transitions

**Standard Transitions:**
- Duration: 150ms (fast), 300ms (normal)
- Easing: ease-in-out

**Hover States:**
```css
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
```

---

## Accessibility

**Requirements:**
- Color contrast: WCAG 2.1 AA (4.5:1 for text)
- Focus states: Visible focus rings
- Touch targets: Minimum 44x44px
- Screen reader support: Proper ARIA labels

**Focus Visible:**
```css
:focus-visible {
    outline: 2px solid var(--tblr-primary);
    outline-offset: 2px;
}
```

---

## Best Practices

1. **Consistency:** Use existing components from `platform/core/base/resources/views/components/`
2. **Responsiveness:** Always design mobile-first
3. **Feedback:** Provide visual feedback for all interactions
4. **Loading States:** Show loading indicators for async operations
5. **Error Handling:** Display clear error messages with recovery actions
6. **Empty States:** Always handle empty data scenarios gracefully

---

## Reference

- [Tabler UI Documentation](https://docs.tabler.io/ui)
- [Bootstrap 5.3 Documentation](https://getbootstrap.com/docs/5.3)
- [Tabler Icons](https://tabler-icons.io/)
