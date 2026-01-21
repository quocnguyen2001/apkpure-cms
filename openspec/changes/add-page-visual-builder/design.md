# Page Visual Builder - Technical Design

## Context

The current page editing workflow requires users to edit shortcodes through CKEditor's modal interface, then save and navigate to the frontend to see results. This creates unnecessary friction and slows down content creation. The visual builder aims to provide a WordPress Customizer-like experience with live preview and intuitive editing controls.

### Constraints
- Must work with existing shortcode system without breaking changes
- Should leverage existing guideline link infrastructure where possible
- Need to maintain compatibility with all registered shortcodes
- Must support pages only (not blog posts or other content types initially)
- Should handle both simple and complex shortcodes with nested content

### Stakeholders
- Content editors who create and maintain pages
- Developers who register custom shortcodes
- Theme developers who design page layouts

## Goals / Non-Goals

### Goals
- Provide intuitive visual editing interface for page shortcodes
- Enable live preview of shortcode changes without page reload
- Support add/edit/reorder/delete operations on shortcodes
- Maintain existing shortcode registration and rendering patterns
- Create reusable architecture that could extend to other content types

### Non-Goals
- Full page HTML editing (only shortcode blocks)
- Inline text editing within shortcodes (edit through forms only)
- Undo/redo history (defer to future iteration)
- Collaborative real-time editing (not in scope)
- Support for non-page content types (blog posts, etc.) in initial version
- Mobile device support for visual builder UI (desktop/tablet only)

## Decisions

### Decision 1: WordPress Customizer-Style Split Screen

**Choice**: Full-screen interface with fixed left sidebar (30-40% width) and right iframe preview (60-70% width).

**Rationale**:
- Proven UX pattern familiar to WordPress users
- Clear separation between controls and preview
- Allows full context for preview while keeping controls accessible
- Avoids complex modal/overlay interactions

**Alternatives Considered**:
- **Inline editing with floating toolbar**: Rejected due to complexity and limited space for form controls
- **Top/bottom split**: Rejected because vertical space is more constrained than horizontal
- **Modal-based editing**: Rejected because it hides the preview while editing

### Decision 2: jQuery for DOM Manipulation and State Management

**Choice**: Build visual builder using jQuery for DOM manipulation with a simple JavaScript object-based state management pattern.

**Rationale**:
- jQuery already heavily used throughout Botble CMS admin panel
- Simpler learning curve for developers already familiar with the codebase
- Lighter bundle size compared to full framework
- Direct DOM manipulation is sufficient for this feature's complexity
- Existing CMS admin JavaScript uses jQuery patterns extensively
- No need for build complexity or additional dependencies

**Alternatives Considered**:
- **Vue.js 3 with Pinia**: Rejected to avoid adding framework complexity and additional build dependencies
- **Vanilla JavaScript**: Could work but jQuery provides cleaner cross-browser API
- **Alpine.js**: Rejected as it's not currently used in the CMS and would add new dependency

### Decision 3: Data Attribute Injection for Shortcode Identification

**Choice**: Extend `shortcode_content_compiled` filter to inject `data-shortcode-id`, `data-shortcode-name`, and `data-shortcode-position` attributes into rendered HTML.

**Rationale**:
- Leverages existing filter hook system
- Non-invasive approach that doesn't modify core shortcode rendering
- Provides reliable way to match DOM elements to shortcode data
- Compatible with all shortcode types (no shortcode-specific changes needed)

**Alternatives Considered**:
- **CSS class-based identification**: Rejected because class names may conflict with theme styles
- **Regex parsing of rendered HTML**: Rejected due to fragility and complexity
- **Wrapping all shortcodes in containers**: Rejected because it modifies HTML structure and may break layouts

### Decision 4: Shortcode Parser Service (Parse Before Render)

**Choice**: Create a `ShortcodeParserService` that parses raw page content to extract all shortcodes before rendering, building a structured map of shortcode data.

**Rationale**:
- Provides source of truth for shortcode positions and attributes
- Enables accurate reordering and insertion of new shortcodes
- Separates parsing logic from rendering for better testability
- Allows validation of shortcode syntax before save

**Alternatives Considered**:
- **Parse from rendered HTML**: Rejected because information loss (attributes become HTML, structure changes)
- **Use ShortcodeCompiler directly**: Rejected because it's designed for rendering, not data extraction

### Decision 5: PostMessage API for Iframe Communication

**Choice**: Use `window.postMessage()` API for communication between parent window (visual builder) and iframe (preview).

**Rationale**:
- Standard browser API for cross-origin communication
- Secure message passing with origin verification
- Supports bidirectional communication (parent ↔ iframe)
- Works reliably across all modern browsers

**Alternatives Considered**:
- **Direct iframe.contentWindow access**: Rejected due to same-origin policy restrictions
- **Polling/URL hash changes**: Rejected as fragile and performance-intensive
- **WebSockets**: Rejected as over-engineered for this use case

### Decision 6: Staged Changes Pattern (Save Button vs Auto-Save)

**Choice**: Implement explicit save button that commits all changes at once, with optional auto-save draft every 30 seconds.

**Rationale**:
- Gives users control over when changes are persisted
- Matches existing CMS form behavior (explicit save)
- Prevents accidental content loss from rapid edits
- Auto-save draft provides safety net without forced commits

**Alternatives Considered**:
- **Auto-save every change**: Rejected because creates excessive database writes and may save incomplete edits
- **No auto-save**: Rejected because risk of data loss on browser crash/close

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                    Page Edit Form                           │
│  ┌────────────────────────────────────────────────────┐    │
│  │  [Visual Builder Button]  [UI Block]  [Save]       │    │
│  └────────────────────────────────────────────────────┘    │
│                           │                                  │
│                           ▼ (opens new route)                │
└─────────────────────────────────────────────────────────────┘
                            │
┌───────────────────────────▼──────────────────────────────────┐
│              Visual Builder Interface                         │
│  ┌─────────────────────────────────────────────────────────┐│
│  │  Header: [Page Title]  [Close]  [Save]                  ││
│  └─────────────────────────────────────────────────────────┘│
│  ┌──────────┬──────────────────────────────────────────────┐│
│  │ Sidebar  │ Iframe Preview                               ││
│  │          │ ┌────────────────────────────────────────┐   ││
│  │ Shortcode││ │ Page Content                           │   ││
│  │ List     ││ │  ┌───────────────┐ [edit icon]        │   ││
│  │  - Media ││ │  │ Shortcode 1   │                     │   ││
│  │  - CTA   ││ │  └───────────────┘                     │   ││
│  │  - FAQ   ││ │  ┌───────────────┐ [edit icon]        │   ││
│  │          ││ │  │ Shortcode 2   │ ◄─── PostMessage   │   ││
│  │ [+ Add]  ││ │  └───────────────┘                     │   ││
│  │          ││ └────────────────────────────────────────┘   ││
│  │ Edit Form││   ▲                                          ││
│  │ (dynamic)││   │ PostMessage updates                      ││
│  └──────────┴──────────────────────────────────────────────┘│
└───────────────────────────────────────────────────────────────┘
```

### Data Flow

#### 1. Loading Visual Builder
```
User clicks "Visual Builder" button
    ↓
PageController@visualBuilder
    ↓
Parse raw content with ShortcodeParserService
    ↓
Build shortcode map: { id, name, attributes, position, content }
    ↓
Render visual-builder.blade.php with initial state
    ↓
Vue app mounts, initializes Pinia store with shortcode data
    ↓
Iframe loads preview route: /pages/{id}/preview?visual_builder=1
    ↓
ShortcodeCompiler injects data attributes during rendering
    ↓
Iframe sends "ready" message to parent
    ↓
Parent injects edit icon overlays into iframe
```

#### 2. Editing Shortcode
```
User clicks pencil icon on shortcode block
    ↓
Iframe sends "edit-shortcode" message to parent with shortcode ID
    ↓
Parent opens sidebar panel for that shortcode
    ↓
User changes form field
    ↓
JavaScript state object updates shortcode data
    ↓
Parent sends "update-shortcode" message to iframe
    ↓
Iframe applies changes to specific shortcode block
    (if shortcode supports live updates, re-renders that block)
```

#### 3. Reordering Shortcodes
```
User drags shortcode in sidebar list
    ↓
Sortable.js fires reorder event
    ↓
JavaScript state object updates shortcode order
    ↓
Parent sends "reorder-shortcodes" message to iframe
    ↓
Iframe re-renders entire page with new order
```

#### 4. Saving Changes
```
User clicks "Save" button
    ↓
Serialize JavaScript state object back to shortcode syntax
    ↓
POST /pages/{id}/visual-builder/save
    ↓
PageController@saveVisualBuilder validates and updates page.content
    ↓
Return success response
    ↓
Redirect to page edit form with success message
```

### File Structure

```
platform/packages/page/
├── src/
│   ├── Http/Controllers/
│   │   └── PageController.php (add visualBuilder, preview, saveVisualBuilder methods)
│   ├── Services/
│   │   ├── PageService.php (enhance preview mode)
│   │   └── ShortcodeParserService.php (NEW - parse raw content)
│   ├── Forms/
│   │   └── PageForm.php (add Visual Builder button)
│   └── Http/Requests/
│       └── SaveVisualBuilderRequest.php (NEW - validation)
├── resources/
│   ├── views/
│   │   └── visual-builder/
│   │       ├── index.blade.php (main layout with all HTML)
│   │       ├── header.blade.php
│   │       └── preview-frame.blade.php
│   ├── js/
│   │   └── visual-builder/
│   │       ├── app.js (main jQuery application)
│   │       ├── state.js (simple state management object)
│   │       ├── sidebar.js (sidebar UI logic)
│   │       ├── shortcode-list.js (list rendering and drag-drop)
│   │       ├── edit-panel.js (dynamic form rendering)
│   │       ├── add-modal.js (add shortcode modal)
│   │       ├── preview-iframe.js (iframe management)
│   │       └── utils/
│   │           ├── postmessage.js
│   │           ├── shortcode-serializer.js
│   │           └── inject-edit-icons.js
│   └── sass/
│       └── visual-builder/
│           ├── _layout.scss
│           ├── _sidebar.scss
│           ├── _iframe.scss
│           └── _edit-icons.scss
└── routes/
    └── web.php (add visual builder routes)
```

## Risks / Trade-offs

### Risk 1: Iframe Same-Origin Issues
**Risk**: Iframe may have cross-origin restrictions if preview uses different domain.
**Mitigation**: Ensure preview route uses same origin as admin panel. Use PostMessage API which handles CORS properly.

### Risk 2: Complex Shortcode Rendering
**Risk**: Some shortcodes may not support partial re-rendering or have dependencies that break in iframe context.
**Mitigation**: Fall back to full iframe reload if live update fails. Document shortcode best practices for visual builder compatibility.

### Risk 3: Performance with Many Shortcodes
**Risk**: Pages with 20+ shortcodes may be slow to parse and render.
**Mitigation**: Implement lazy loading for shortcode previews. Add loading indicators. Consider pagination for shortcode list if needed.

### Risk 4: Browser Compatibility
**Risk**: PostMessage API and modern CSS (Grid/Flexbox) may not work in older browsers.
**Mitigation**: Set minimum browser requirements (modern browsers only). Provide clear error message for unsupported browsers.

### Trade-off 1: Form-Based Editing vs Inline Editing
**Decision**: Use form-based editing in sidebar rather than inline WYSIWYG.
**Benefit**: Simpler implementation, consistent with existing CMS patterns, works for all shortcode types.
**Cost**: Less intuitive than direct manipulation, requires switching context between sidebar and preview.

### Trade-off 2: Full-Screen Interface vs Modal/Panel
**Decision**: Use dedicated full-screen route rather than modal overlay.
**Benefit**: More space for controls and preview, clearer user intent (entering "builder mode").
**Cost**: Requires navigation away from edit form, need to handle unsaved changes warning.

## Migration Plan

### Phase 1: Core Infrastructure (Week 1-2)
1. Create ShortcodeParserService with tests
2. Add data attribute injection to shortcode rendering
3. Build basic split-screen layout with Vue app scaffold
4. Implement PostMessage communication layer

### Phase 2: Edit Functionality (Week 3-4)
5. Create shortcode list view in sidebar
6. Build dynamic form renderer for shortcode editing
7. Implement edit icon injection in iframe
8. Add live preview updates for simple shortcodes

### Phase 3: Management Features (Week 5-6)
9. Add "Add Shortcode" modal with type selection
10. Implement drag-and-drop reordering with Sortable.js
11. Add delete functionality with confirmation
12. Build content serializer for save operation

### Phase 4: Integration & Testing (Week 7-8)
13. Add Visual Builder button to PageForm
14. Implement save/close workflow
15. Add responsive device switcher (desktop/tablet/mobile)
16. Write comprehensive tests (unit, integration, E2E)
17. Performance optimization and bug fixes

### Rollback Plan
If critical issues arise:
1. Feature flag: Add `ENABLE_PAGE_VISUAL_BUILDER` setting (default: false)
2. Remove Visual Builder button from PageForm when disabled
3. Routes return 404 when feature disabled
4. No database changes, so can safely toggle on/off

### Monitoring
- Track visual builder usage (page visits to `/admin/pages/*/visual-builder`)
- Monitor save success/failure rates
- Collect user feedback through admin notice banner
- Log JavaScript errors from visual builder for debugging

## Open Questions

### Question 1: Shortcode Discovery
How do users discover available shortcode types when adding new ones?
- **Option A**: Full list with search/filter
- **Option B**: Categorized by type (Content, Media, Form, etc.)
- **Option C**: Recently used + full list
- **Decision needed**: User research on preferred discovery method

### Question 2: Nested Shortcodes
How to handle shortcodes that contain other shortcodes (nested)?
- **Option A**: Flatten in list view, show hierarchy with indentation
- **Option B**: Expandable tree view in sidebar
- **Option C**: Only allow editing parent, child shortcodes read-only
- **Decision needed**: Determine complexity vs. user need

### Question 3: Undo/Redo
Should we implement undo/redo for visual builder actions?
- **Option A**: Full undo/redo stack (complex state management)
- **Option B**: Single-level undo (simple, covers most cases)
- **Option C**: No undo, rely on explicit save (simplest)
- **Decision needed**: Balance complexity vs. user expectations

### Question 4: Keyboard Shortcuts
What keyboard shortcuts should be supported?
- Minimum: Cmd/Ctrl+S for save, Esc to close
- Nice to have: Arrow keys for navigation, Cmd/Ctrl+Z for undo
- **Decision needed**: Prioritize based on development time

### Question 5: Mobile Editing
Should visual builder work on tablets/mobile devices?
- **Current decision**: Desktop only (1024px+ screen width)
- **Future consideration**: Touch-optimized interface for tablets
- **Not planned**: Phone support (screen too small)
