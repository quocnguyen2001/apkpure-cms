# APKPure CMS Documentation

**Last Updated:** 2026-01-27

---

## Quick Navigation

### Theme Documentation

- **[Theme APKPure Phase 1](./theme-apkpure-phase1.md)** - Complete Phase 1 implementation details
  - Theme metadata & configuration
  - External dependencies (Bootstrap, icons, jQuery)
  - Media sizes & asset pipeline
  - Page structure & layout organization

- **[Theme Integration Guide](./theme-integration-guide.md)** - Developer integration & customization
  - Quick start installation
  - Asset loading & CSS pipeline
  - Template variables reference
  - Common customizations
  - Troubleshooting guide

### Design & Standards

- **[Design Guidelines](./design-guidelines.md)** - Botble CMS admin UI standards (Bootstrap 5.3.7 + Tabler)
  - Color system
  - Typography & spacing
  - Card & button components
  - Grid system & responsive design
  - Accessibility requirements

---

## Project Structure

```
./docs/
├── README.md                      # This file
├── design-guidelines.md           # Admin UI design standards
├── theme-apkpure-phase1.md       # Phase 1 implementation
└── theme-integration-guide.md    # Developer guide
```

---

## Phase 1 Overview

**Theme:** APKPure (wallis/apkpure v1.0.0)

**Key Components:**
- Bootstrap 5.3.3 + Bootstrap Icons 1.11.3 + jQuery 3.7.1
- 4 media sizes (icons, screenshots, banners)
- 5 frontend pages (homepage, apps list, app detail, versions, games)
- Primary sidebar for widgets
- 8 theme support features enabled

**Pages Documented:**
- Homepage
- Apps browsing & filtering
- App detail with related apps
- App version history
- Games category

---

## For Developers

### First Time Setup
1. Read [Theme Integration Guide](./theme-integration-guide.md) - Quick Start section
2. Build assets: `npm run production`
3. Activate theme in admin panel
4. Verify pages load correctly

### Making Changes
1. Modify SCSS in `platform/themes/apkpure/assets/sass/`
2. Update HTML templates in `platform/themes/apkpure/views/`
3. Run: `npm run dev` (development) or `npm run production` (deployment)
4. Check [Customization](./theme-integration-guide.md#customization) section for patterns

### Adding Features
- See "Common Modifications" in [Integration Guide](./theme-integration-guide.md#common-modifications)
- Register custom widgets in apkpure-crawler plugin
- Add sidebars in `functions.php`

---

## For Project Managers

**Phase 1 Status:** Completed
- ✓ Theme foundation implemented
- ✓ All dependencies integrated
- ✓ Media sizes defined
- ✓ Frontend pages created
- ✓ Build process configured

**Phase 2+ Roadmap:** See [theme-apkpure-phase1.md](./theme-apkpure-phase1.md#next-steps-phase-2)
- Custom widgets & recommendations
- Advanced filtering system
- User ratings & reviews
- Download tracking
- Multi-language support
- Dark mode variant

---

## References

- [Botble CMS Documentation](https://botble.com/docs)
- [Bootstrap 5.3 Docs](https://getbootstrap.com/docs/5.3)
- [Bootstrap Icons](https://icons.getbootstrap.com)
- [Laravel Mix](https://laravel-mix.com)

---

## Support

For issues or questions:
1. Check [Troubleshooting](./theme-integration-guide.md#troubleshooting) section
2. Review template variable documentation
3. Check browser console for JS errors
4. Verify all assets are loaded (DevTools → Network)

