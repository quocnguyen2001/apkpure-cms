# Implementation Tasks

## Phase 1: Backend Foundation

- [ ] 1.1 Create `ShortcodeParserService` class in `platform/packages/page/src/Services/`
  - Parse raw content with regex to extract all shortcodes
  - Build structured data: `{ id, name, attributes, position, content, rawMatch }`
  - Handle nested shortcodes recursively
  - Write unit tests for various shortcode formats

- [ ] 1.2 Add data attribute injection to shortcode rendering
  - Modify `platform/packages/shortcode/src/Compilers/ShortcodeCompiler.php`
  - Detect `?visual_builder=1` query parameter in request
  - Use `shortcode_content_compiled` filter (priority 9999)
  - Inject `data-shortcode-id`, `data-shortcode-name`, `data-shortcode-position`
  - Ensure attributes added to top-level HTML element
  - Test with simple and complex shortcode HTML structures

- [ ] 1.3 Add visual builder routes to `platform/packages/page/routes/web.php`
  - `GET /admin/pages/{id}/visual-builder` → `PageController@visualBuilder`
  - `GET /admin/pages/{id}/preview` → `PageController@preview`
  - `POST /admin/pages/{id}/visual-builder/save` → `PageController@saveVisualBuilder`
  - Apply `pages.edit` permission middleware

- [ ] 1.4 Implement `PageController@visualBuilder` method
  - Validate page exists and user has permission
  - Parse page content with `ShortcodeParserService`
  - Pass shortcode data and page info to view
  - Return `visual-builder.index` view

- [ ] 1.5 Implement `PageController@preview` method
  - Load page with `PageService::handleFrontRoutes()`
  - Set `visual_builder` flag in view data
  - Render page with template (iframe-ready HTML)
  - Ensure no admin bar or other admin UI elements

- [ ] 1.6 Implement `PageController@saveVisualBuilder` method
  - Create `SaveVisualBuilderRequest` for validation
  - Receive serialized content from frontend
  - Update page content field
  - Return JSON success/error response

- [ ] 1.7 Create content serializer utility (PHP helper)
  - Convert shortcode data array back to text format
  - Handle attributes with special characters (escape quotes)
  - Support self-closing and nested shortcodes
  - Write unit tests for serialization accuracy

## Phase 2: Visual Builder Layout & jQuery App

- [ ] 2.1 Create blade layout at `platform/packages/page/resources/views/visual-builder/index.blade.php`
  - Full-screen HTML structure (no admin layout wrapper)
  - Header bar with page title, close, save buttons
  - Split-screen container (sidebar + iframe) with complete HTML markup
  - Sidebar with shortcode list container, edit panel container, add button
  - Iframe preview container
  - Pass initial data via JSON in script tag: `window.visualBuilderData = @json($data)`

- [ ] 2.2 Create header component blade partial `visual-builder/header.blade.php`
  - Page title display
  - Close button with unsaved changes warning
  - Save button with loading state
  - Device mode selector (Desktop/Tablet/Mobile) as button group

- [ ] 2.3 Create iframe preview blade view `visual-builder/preview-frame.blade.php`
  - Simple container with loading spinner
  - iframe element with sandbox attributes
  - Error display area for load failures

- [ ] 2.4 Create state management JavaScript at `js/visual-builder/state.js`
  - Simple object-based state: `VisualBuilderState`
  - Properties: `shortcodes`, `activeShortcode`, `hasChanges`, `isSaving`, `deviceMode`
  - Methods: `init()`, `getShortcode()`, `updateShortcode()`, `addShortcode()`, `deleteShortcode()`, `reorderShortcodes()`, `setActive()`, `markChanged()`
  - Event emitter pattern for change notifications

- [ ] 2.5 Create main jQuery application at `js/visual-builder/app.js`
  - Initialize on document ready
  - Load initial data from `window.visualBuilderData`
  - Initialize state object
  - Set up event handlers for header buttons
  - Initialize all modules (sidebar, list, panel, iframe)
  - Handle unsaved changes warning on close

- [ ] 2.6 Create visual builder SCSS at `resources/sass/visual-builder/`
  - `_layout.scss`: Split-screen grid, header bar, full-screen styles
  - `_sidebar.scss`: Sidebar panels, forms, list view
  - `_iframe.scss`: Preview container, device mode frames
  - `_edit-icons.scss`: Pencil icon overlays (reuse guideline.css patterns)
  - Main file: `visual-builder.scss` imports all partials

- [ ] 2.7 Enqueue assets in PageController@visualBuilder
  - Add jQuery (if not already loaded)
  - Add Sortable.js library
  - Add `visual-builder.js` and `visual-builder.css` using Assets facade
  - Ensure proper loading order

## Phase 3: Sidebar jQuery Modules

- [ ] 3.1 Create sidebar manager at `js/visual-builder/sidebar.js`
  - Module: `VisualBuilderSidebar`
  - Methods: `init()`, `showList()`, `showEditPanel()`, `showAddModal()`, `hide()`
  - Toggle between list view and edit panel view
  - Handle "Add Shortcode" button click

- [ ] 3.2 Create shortcode list module at `js/visual-builder/shortcode-list.js`
  - Module: `ShortcodeList`
  - Methods: `render()`, `renderItem()`, `attachEvents()`, `initDragDrop()`
  - Render shortcode list HTML dynamically from state
  - Each item: icon, name, mini preview, delete button
  - Click item to open edit panel
  - Emit events: `item-click`, `item-delete`

- [ ] 3.3 Integrate Sortable.js for drag-and-drop
  - Initialize Sortable on shortcode list container
  - Handle `onEnd` event to update state order
  - Update list DOM after reorder
  - Send PostMessage to reload iframe preview

- [ ] 3.4 Create edit panel module at `js/visual-builder/edit-panel.js`
  - Module: `ShortcodeEditPanel`
  - Methods: `render()`, `renderField()`, `attachEvents()`, `getValues()`, `close()`
  - Dynamic form field rendering based on shortcode schema
  - Debounced input handling (300ms delay using `setTimeout`)
  - Support field types: text, textarea, select, checkbox, radio, media picker, color picker
  - Emit: `update`, `close`
  - Show shortcode name/type as header

- [ ] 3.5 Create add shortcode modal at `js/visual-builder/add-modal.js`
  - Module: `AddShortcodeModal`
  - Methods: `show()`, `hide()`, `renderShortcodeTypes()`, `renderForm()`, `submit()`
  - Display available shortcode types from `window.visualBuilderData.availableShortcodes`
  - Search/filter functionality using jQuery filter
  - On type select, show configuration form
  - Position selector: dropdown to choose insert position
  - Submit button adds shortcode to state and refreshes list

- [ ] 3.6 Create form field renderer utility at `js/visual-builder/utils/form-fields.js`
  - Module: `FormFieldRenderer`
  - Method: `render(fieldType, fieldName, fieldValue, fieldOptions)` returns jQuery element
  - Support all field types with proper Tabler UI classes
  - Integrate with existing CMS media picker, color picker widgets
  - Handle validation attributes (required, maxlength, pattern)

## Phase 4: Preview & Communication

- [ ] 4.1 Create preview iframe module at `js/visual-builder/preview-iframe.js`
  - Module: `PreviewIframe`
  - Properties: `$iframe` (jQuery element), `isReady`, `deviceMode`
  - Methods: `init()`, `load()`, `reload()`, `setDeviceMode()`, `showLoading()`, `hideLoading()`, `showError()`
  - Apply device width based on mode (desktop: 100%, tablet: 768px, mobile: 375px)
  - Center narrow frames with gray background
  - Display loading spinner and error messages

- [ ] 4.2 Create PostMessage utility at `js/visual-builder/utils/postmessage.js`
  - `sendToIframe(iframe, type, payload)`: Send message with origin check
  - `listenToIframe(callback)`: Set up message listener with origin validation
  - `sendToParent(type, payload)`: For iframe-side usage (inline script)
  - Message types: `ready`, `edit-shortcode`, `update-shortcode`, `reorder-shortcodes`, `reload-preview`
  - Origin validation for security

- [ ] 4.3 Inject edit icons into iframe (parent-side)
  - Create `js/visual-builder/utils/inject-edit-icons.js`
  - Module: `EditIconInjector`
  - Method: `inject(iframeDocument)`: Query all `[data-shortcode-id]` elements
  - For each element, inject pencil icon HTML (top-right positioned)
  - Attach click handlers that send `edit-shortcode` PostMessage to parent
  - Apply hover effects using CSS (opacity transition)
  - Handle z-index and positioning edge cases

- [ ] 4.4 Handle iframe-side PostMessage receiving
  - Create inline script in preview blade view (`visual-builder/preview-frame.blade.php`)
  - Listen for messages from parent window
  - Handle `update-shortcode`: Find element by `data-shortcode-id`, update if possible
  - Handle `reorder-shortcodes`: Full iframe reload
  - Handle `highlight-shortcode`: Add active class to element
  - Send `ready` message when page finishes loading

- [ ] 4.5 Implement live preview updates (parent-side in app.js)
  - When shortcode form field changes, debounce update
  - Send `update-shortcode` message to iframe with new data
  - For simple shortcodes (text/image changes), attempt partial update
  - For complex shortcodes (structure changes), reload entire iframe
  - Show loading indicator during update

- [ ] 4.6 Implement active shortcode highlighting
  - When shortcode selected from list or clicked in iframe, store active ID
  - Send `highlight-shortcode` message to iframe with ID
  - Iframe adds `.vb-active` class to element (distinct border/background via CSS)
  - Remove `.vb-active` class from previous element
  - Clear highlight when edit panel closes

## Phase 5: Shortcode Management

- [ ] 5.1 Implement add shortcode flow (in app.js coordinating modules)
  - User clicks "Add Shortcode" button
  - `AddShortcodeModal.show()` displays available types
  - User selects type, form renders with attributes
  - User fills form and chooses insertion position
  - On submit: generate shortcode ID (timestamp-based unique ID)
  - `VisualBuilderState.addShortcode()` adds to state at position
  - `ShortcodeList.render()` refreshes list
  - `PreviewIframe.reload()` shows new shortcode in preview

- [ ] 5.2 Implement reorder shortcode flow
  - Sortable.js fires `onEnd` event when user drags item
  - Get new order from list DOM
  - `VisualBuilderState.reorderShortcodes(newOrder)` updates state
  - `ShortcodeList.render()` updates list display
  - Send `reorder-shortcodes` PostMessage to iframe
  - Iframe fully reloads with new content order

- [ ] 5.3 Implement delete shortcode flow
  - User clicks delete button on list item
  - Show Bootstrap/Tabler confirmation dialog: "Are you sure you want to delete this shortcode?"
  - On confirm: `VisualBuilderState.deleteShortcode(id)` removes from state
  - `ShortcodeList.render()` updates list
  - `PreviewIframe.reload()` shows preview without deleted shortcode

- [ ] 5.4 Create content serializer (JavaScript) at `js/visual-builder/utils/shortcode-serializer.js`
  - Module: `ShortcodeSerializer`
  - Method: `serialize(shortcodes)`: Convert array to shortcode syntax string
  - Handle attributes escaping (quotes, special chars)
  - Support self-closing tags: `[shortcode /]`
  - Support nested content: `[shortcode]content[/shortcode]`
  - Preserve non-shortcode HTML between shortcodes (future enhancement)
  - Return complete page content string

- [ ] 5.5 Implement save workflow (in app.js)
  - User clicks "Save" button in header
  - Serialize current state with `ShortcodeSerializer.serialize()`
  - Show loading state on save button (disable + spinner)
  - AJAX POST to `/admin/pages/{id}/visual-builder/save` with serialized content and CSRF token
  - On success: Show success notification (Toastr or similar), mark `hasChanges = false`
  - On error: Show error notification with message, re-enable save button
  - Optionally: redirect to page edit form after successful save

## Phase 6: Form Integration

- [ ] 6.1 Add Visual Builder button to `PageForm.php`
  - Add after content field using `addAfter()` or custom HTML field
  - Button HTML: `<a href="{{ route('pages.visual-builder', $page->id) }}" class="btn btn-info"><i class="ti ti-layout-dashboard"></i> Visual Builder</a>`
  - Only show button when editing existing page (not on create)
  - Position near UI Block button for easy discovery

- [ ] 6.2 Register visual builder assets in PageController@visualBuilder
  - Enqueue `visual-builder.js` and `visual-builder.css` on visual builder route only
  - Use `Assets::addScripts()` and `Assets::addStyles()`
  - Ensure dependencies loaded: jQuery (already in admin), Sortable.js
  - Load in correct order: jQuery → Sortable → visual-builder.js

- [ ] 6.3 Handle unsaved changes warning (in app.js)
  - Listen for `beforeunload` event when `hasChanges === true`
  - Show browser confirmation dialog: "You have unsaved changes. Are you sure you want to leave?"
  - Prevent accidental data loss
  - Clear warning when changes saved successfully

## Phase 7: Auto-Save & Drafts

- [ ] 7.1 Implement auto-save timer (in app.js)
  - Start 30-second `setInterval` on first edit
  - Serialize state with `ShortcodeSerializer.serialize()`
  - Save to localStorage with key `vb_draft_{pageId}`
  - Show subtle "Auto-saving..." indicator in header (fade in/out)

- [ ] 7.2 Implement draft restoration (in app.js init)
  - On visual builder load, check localStorage for `vb_draft_{pageId}`
  - Parse draft and compare timestamp with page updated_at
  - Show modal prompt: "Restore unsaved changes from [formatted timestamp]?" with "Restore" / "Discard" buttons
  - If restore: load draft data into `VisualBuilderState`, render list and preview
  - If discard: delete localStorage key

- [ ] 7.3 Clear draft on explicit save (in save workflow)
  - After successful AJAX save response
  - Delete localStorage key: `localStorage.removeItem('vb_draft_{pageId}')`
  - Clear auto-save interval
  - Reset hasChanges flag

## Phase 8: Device Preview Modes

- [ ] 8.1 Add device mode selector to header (in header.blade.php)
  - Three buttons: Desktop (default), Tablet, Mobile icons
  - Use button group with active state styling
  - Add click handlers in app.js to change mode

- [ ] 8.2 Implement iframe width adjustment (in PreviewIframe module)
  - Method: `setDeviceMode(mode)`
  - Desktop: iframe container 100% width, iframe 100%
  - Tablet: iframe container 100%, inner wrapper 768px centered, gray background
  - Mobile: iframe container 100%, inner wrapper 375px centered, gray background
  - Use jQuery `.css()` or `.addClass()` for smooth transitions

- [ ] 8.3 Adjust edit icons for device modes (in inject-edit-icons.js)
  - Maintain icon size and positioning across all modes
  - Ensure icons remain clickable and visible
  - Test hover states work properly in narrow views

## Phase 9: Error Handling

- [ ] 9.1 Add global error handler (in app.js)
  - Wrap main code in try-catch
  - Use `window.onerror` to catch unhandled errors
  - Log to console for debugging
  - Show user-friendly notification using Toastr or similar

- [ ] 9.2 Handle save request errors (in save workflow)
  - Network failures (AJAX error): Show "Failed to save. Please try again." with retry button
  - Validation errors (422 response): Parse JSON errors, display messages in notification
  - Server errors (500): Show "An error occurred. Please contact support."
  - Re-enable save button and remove loading state

- [ ] 9.3 Handle iframe load errors (in PreviewIframe module)
  - Listen for iframe `error` event
  - Detect 404, 500, CORS issues from load failure
  - Display error message in iframe container: "Unable to load preview."
  - Provide "Reload Preview" button that calls `PreviewIframe.reload()`

- [ ] 9.4 Validate shortcode syntax before save (in save workflow)
  - Basic validation: check serialized content for obvious issues
  - Check for unclosed brackets, malformed attributes
  - If validation fails, show specific error in notification
  - Allow user to review state and fix issues before retry

## Phase 10: Testing & Quality

- [ ] 10.1 Write PHP unit tests
  - `ShortcodeParserService` tests: various shortcode formats, nested, edge cases
  - Content serializer tests: roundtrip accuracy (parse → serialize → parse)
  - Controller tests: routes return correct responses, permissions enforced

- [ ] 10.2 Write JavaScript unit tests (optional, if test framework available)
  - Test `ShortcodeSerializer.serialize()` with various inputs
  - Test state management object methods
  - Test PostMessage utility functions
  - Mock iframe interactions if possible

- [ ] 10.3 Manual testing scenarios
  - Test with 0 shortcodes (empty content)
  - Test with 1 shortcode
  - Test with 10+ shortcodes
  - Test with nested shortcodes
  - Test all device modes
  - Test in different browsers (Chrome, Firefox, Safari)
  - Test slow network conditions
  - Test with user lacking permissions

- [ ] 10.4 Run Pint on all PHP files
  - `./vendor/bin/pint platform/packages/page/src/`
  - Fix any PSR-12 violations

- [ ] 10.5 Performance optimization
  - Minimize bundle size for visual-builder.js
  - Lazy load AddShortcodeModal component
  - Debounce preview updates properly
  - Test with large pages (50+ shortcodes)

## Phase 11: Documentation & Polish

- [ ] 11.1 Add inline code comments
  - Document complex logic in parser, serializer
  - Explain PostMessage message formats
  - Add JSDoc comments to JavaScript functions

- [ ] 11.2 Create user documentation (optional)
  - How to access visual builder
  - How to edit, add, reorder, delete shortcodes
  - Device preview modes
  - Auto-save and drafts
  - Screenshot/GIF demonstrations

- [ ] 11.3 UI polish
  - Smooth animations and transitions
  - Loading states for all async operations
  - Proper focus management (keyboard navigation)
  - Accessible labels and ARIA attributes
  - Consistent spacing and typography (match Tabler UI)

- [ ] 11.4 Browser testing and fixes
  - Test in Chrome, Firefox, Safari, Edge
  - Fix any browser-specific issues
  - Verify PostMessage works across all browsers

- [ ] 11.5 Final QA pass
  - Test all requirements from spec.md
  - Verify all scenarios pass
  - Check error handling for edge cases
  - Performance check with large content

## Phase 12: Deployment

- [ ] 12.1 Build production assets
  - Run `npm run prod` to build minified JS/CSS
  - Verify asset paths work in production environment

- [ ] 12.2 Database migration (if needed)
  - Create migration for any new columns/tables
  - No database changes expected for initial version

- [ ] 12.3 Clear caches
  - `php artisan cache:clear`
  - `php artisan view:clear`
  - `php artisan config:clear`

- [ ] 12.4 Update CHANGELOG
  - Add entry: "Added visual builder for pages with shortcode editing capabilities"

- [ ] 12.5 Deploy to staging environment
  - Test visual builder on staging
  - Gather feedback from internal users
  - Fix any issues discovered

- [ ] 12.6 Deploy to production
  - Schedule maintenance window if needed
  - Deploy code and run migrations
  - Verify visual builder works in production
  - Monitor for errors in first 24 hours
