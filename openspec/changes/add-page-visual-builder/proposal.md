# Add Page Visual Builder

## Why

The current page editing experience requires users to edit shortcodes through the CKEditor interface, which lacks visual context and makes content layout difficult to understand. Users cannot see how their shortcodes render on the actual page without saving and previewing separately. This creates friction in the content editing workflow and makes it challenging to build complex page layouts with multiple shortcodes.

A WordPress Customizer-style visual builder solves this by providing a split-screen interface where editors can see live preview of changes while editing shortcode configurations, significantly improving the content creation experience.

## What Changes

- Add "Visual Builder" button on page edit form (near content editor field, next to UI Block button)
- Create new visual builder route at `/admin/pages/{id}/visual-builder` with full-screen interface
- Implement split-screen layout: left sidebar (30-40% width) for controls, right iframe (60-70% width) for live preview
- Inject pencil icon overlays on shortcode blocks in preview (similar to `show_theme_guideline_link` feature)
- Enable click-to-edit: clicking pencil opens relevant shortcode form in sidebar
- Add shortcode management capabilities:
  - Edit existing shortcodes with live preview updates
  - Add new shortcodes with type selection and position control
  - Reorder shortcodes via drag-and-drop in sidebar list
  - Delete shortcodes with confirmation
- Implement save mechanism that updates page content field and returns to edit form
- Build jQuery-based application for visual builder UI with simple state management
- Create backend endpoints for preview mode and save operations
- Enhance shortcode rendering to inject data attributes for visual builder identification

## Impact

### Affected Specs
- **New capability**: `page-visual-builder` - Complete visual editing interface for page shortcodes

### Affected Code
- `platform/packages/page/src/Forms/PageForm.php` - Add Visual Builder button
- `platform/packages/page/src/Http/Controllers/PageController.php` - Add visual builder routes
- `platform/packages/page/routes/web.php` - Register new routes
- `platform/packages/page/src/Services/PageService.php` - Enhance preview mode
- `platform/packages/shortcode/src/Compilers/ShortcodeCompiler.php` - Add data attribute injection
- `platform/packages/page/resources/views/` - New visual builder layout views
- `platform/packages/page/resources/js/` - New Vue.js application
- `platform/packages/page/resources/sass/` - Visual builder styles
- `platform/packages/page/webpack.mix.js` - Build configuration

### Breaking Changes
None. This is a purely additive feature that enhances existing functionality without modifying current behavior.

### Dependencies
- Existing shortcode system (`platform/packages/shortcode/`)
- Existing guideline link infrastructure (`platform/packages/theme/`)
- jQuery (already in use throughout admin panel)
- Sortable.js for drag-and-drop
- Existing Botble CMS form field widgets (media picker, color picker, etc.)

### User-Facing Changes
- New "Visual Builder" button appears on page edit screen
- New visual builder interface accessible for pages with shortcode content
- Improved content editing workflow with live preview
- Faster iteration on page layouts without save/refresh cycles
