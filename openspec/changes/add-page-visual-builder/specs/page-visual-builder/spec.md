# Page Visual Builder Specification

## ADDED Requirements

### Requirement: Visual Builder Button Access
The page edit form SHALL display a "Visual Builder" button near the content editor field that opens the visual builder interface in a new browser context.

#### Scenario: Button appears on page edit form
- **WHEN** an admin user opens the page edit form
- **AND** the user has `pages.edit` permission
- **THEN** a "Visual Builder" button SHALL appear above the content editor field, next to the "UI Block" button

#### Scenario: Button opens visual builder
- **WHEN** user clicks the "Visual Builder" button
- **THEN** the visual builder interface SHALL open at route `/admin/pages/{id}/visual-builder`
- **AND** the interface SHALL display in full-screen mode

#### Scenario: Button hidden for users without permission
- **WHEN** a user without `pages.edit` permission views the page
- **THEN** the "Visual Builder" button SHALL NOT be displayed

### Requirement: Split-Screen Layout
The visual builder SHALL display a split-screen interface with a fixed-width sidebar for controls and an iframe for live preview.

#### Scenario: Layout renders correctly
- **WHEN** the visual builder loads
- **THEN** the interface SHALL display a left sidebar occupying 30-40% of viewport width
- **AND** the right iframe preview SHALL occupy 60-70% of viewport width
- **AND** both panels SHALL span 100% of viewport height minus header

#### Scenario: Sidebar is resizable
- **WHEN** user drags the resize handle between sidebar and iframe
- **THEN** the sidebar width SHALL adjust dynamically between 25% and 50% of viewport width
- **AND** the iframe width SHALL adjust accordingly to fill remaining space

#### Scenario: Responsive behavior on small screens
- **WHEN** viewport width is less than 1024px
- **THEN** the visual builder SHALL display a warning message
- **AND** the warning SHALL suggest using a larger screen for optimal experience

### Requirement: Header Bar Actions
The visual builder SHALL display a header bar with page title, close button, and save button.

#### Scenario: Header displays page information
- **WHEN** the visual builder loads
- **THEN** the header SHALL display the current page title
- **AND** the header SHALL display a close button (X icon or "Close" text)
- **AND** the header SHALL display a "Save" button with primary styling

#### Scenario: Close button returns to edit form
- **WHEN** user clicks the close button
- **AND** there are no unsaved changes
- **THEN** the browser SHALL navigate back to the page edit form

#### Scenario: Close button warns about unsaved changes
- **WHEN** user clicks the close button
- **AND** there are unsaved changes
- **THEN** a confirmation dialog SHALL appear warning about unsaved changes
- **AND** the dialog SHALL offer options: "Save & Close", "Discard Changes", "Cancel"

#### Scenario: Save button persists changes
- **WHEN** user clicks the "Save" button
- **THEN** all shortcode changes SHALL be serialized to shortcode syntax
- **AND** the serialized content SHALL be sent to `/admin/pages/{id}/visual-builder/save`
- **AND** upon success, the page content field SHALL be updated
- **AND** a success notification SHALL be displayed

### Requirement: Iframe Preview Rendering
The visual builder SHALL load the page in an iframe with special visual builder mode enabled for editing capabilities.

#### Scenario: Preview loads current page
- **WHEN** the visual builder initializes
- **THEN** the iframe SHALL load the page at `/admin/pages/{id}/preview?visual_builder=1`
- **AND** the preview SHALL render the page with current content
- **AND** the preview SHALL apply the page's assigned template

#### Scenario: Preview displays loading state
- **WHEN** the iframe is loading
- **THEN** a loading indicator SHALL be displayed over the iframe area
- **AND** sidebar controls SHALL be disabled until preview is ready

#### Scenario: Preview signals ready state
- **WHEN** the iframe completes loading
- **THEN** the iframe SHALL send a "ready" PostMessage to the parent window
- **AND** the parent window SHALL inject edit icon overlays
- **AND** the sidebar controls SHALL become enabled

#### Scenario: Preview handles load errors
- **WHEN** the iframe fails to load (404, 500, network error)
- **THEN** an error message SHALL be displayed in the iframe area
- **AND** the error message SHALL include instructions to check console or refresh

### Requirement: Shortcode Block Indicators
The visual builder SHALL inject pencil icon overlays on each shortcode block in the preview to enable click-to-edit functionality.

#### Scenario: Edit icons appear on shortcode blocks
- **WHEN** the preview is ready
- **AND** the page contains shortcodes
- **THEN** each shortcode block SHALL display a pencil icon overlay in the top-right corner
- **AND** the icons SHALL be hidden by default with `opacity: 0`
- **AND** the icons SHALL appear with `opacity: 1` when hovering over the shortcode block

#### Scenario: Click pencil icon opens edit panel
- **WHEN** user clicks a pencil icon on a shortcode block
- **THEN** the iframe SHALL send an "edit-shortcode" PostMessage with the shortcode ID
- **AND** the parent window SHALL open the edit panel for that shortcode in the sidebar
- **AND** the clicked shortcode block SHALL be highlighted in the preview

#### Scenario: Active shortcode is visually highlighted
- **WHEN** a shortcode is being edited
- **THEN** its block in the preview SHALL have a distinct visual highlight (e.g., colored border)
- **AND** other shortcode blocks SHALL remain in normal state

#### Scenario: Icons work with nested shortcodes
- **WHEN** a page contains nested shortcodes (shortcode within shortcode)
- **THEN** each nested shortcode SHALL have its own pencil icon
- **AND** clicking a nested shortcode icon SHALL edit only that shortcode, not its parent

### Requirement: Sidebar Shortcode List View
The sidebar SHALL display a list of all shortcodes on the page with options to add, reorder, and delete.

#### Scenario: List displays all shortcodes
- **WHEN** the visual builder loads
- **THEN** the sidebar SHALL display a list of all shortcodes in order of appearance
- **AND** each list item SHALL show the shortcode name/type
- **AND** each list item SHALL show a mini preview or icon representing the shortcode

#### Scenario: List supports drag-and-drop reordering
- **WHEN** user drags a shortcode item in the list
- **THEN** the item SHALL follow the cursor
- **AND** other items SHALL shift to show insertion position
- **WHEN** user drops the item in a new position
- **THEN** the shortcode order SHALL update in state
- **AND** the preview SHALL re-render to reflect new order

#### Scenario: Add shortcode button opens modal
- **WHEN** user clicks the "Add Shortcode" button at bottom of list
- **THEN** a modal SHALL open displaying available shortcode types
- **AND** the modal SHALL allow searching/filtering shortcode types
- **WHEN** user selects a shortcode type
- **THEN** the add shortcode form SHALL appear
- **AND** the form SHALL include a position selector (insert after which shortcode)

#### Scenario: Delete shortcode requires confirmation
- **WHEN** user clicks the delete icon on a shortcode list item
- **THEN** a confirmation dialog SHALL appear
- **AND** the dialog SHALL ask "Are you sure you want to delete this shortcode?"
- **WHEN** user confirms
- **THEN** the shortcode SHALL be removed from state
- **AND** the preview SHALL re-render without that shortcode

### Requirement: Dynamic Shortcode Edit Panel
The sidebar SHALL display a dynamic form for editing the selected shortcode's attributes with live preview updates.

#### Scenario: Edit panel opens for selected shortcode
- **WHEN** user selects a shortcode (via list click or preview pencil icon)
- **THEN** the sidebar SHALL display an edit panel for that shortcode
- **AND** the panel SHALL show the shortcode name/type as header
- **AND** the panel SHALL render a form with fields matching the shortcode's registered attributes

#### Scenario: Form fields match shortcode schema
- **WHEN** a shortcode defines attributes (e.g., url, width, height for media shortcode)
- **THEN** the edit panel SHALL render appropriate input fields for each attribute
- **AND** field types SHALL match attribute types (text input, select, checkbox, media picker, etc.)
- **AND** current attribute values SHALL populate the form fields

#### Scenario: Form changes update preview in real-time
- **WHEN** user changes a form field value
- **AND** the change is debounced (300ms delay after last keystroke)
- **THEN** the parent SHALL send an "update-shortcode" PostMessage to iframe
- **AND** the iframe SHALL attempt to update only the affected shortcode block
- **IF** live update is supported for that shortcode type
- **THEN** the shortcode SHALL re-render without full page reload
- **ELSE** the entire iframe SHALL reload with updated content

#### Scenario: Form validation prevents invalid data
- **WHEN** user enters invalid data in a form field
- **THEN** the field SHALL display a validation error message
- **AND** the "Save" button SHALL be disabled until all fields are valid

#### Scenario: Close edit panel returns to list view
- **WHEN** user clicks close icon on edit panel header
- **THEN** the edit panel SHALL close
- **AND** the sidebar SHALL return to showing the shortcode list
- **AND** the active shortcode highlight SHALL be removed from preview

### Requirement: Content Serialization
The visual builder SHALL serialize shortcode state back to valid shortcode syntax for persisting to the database.

#### Scenario: Serialize shortcodes to text format
- **WHEN** user saves changes
- **THEN** the system SHALL iterate through all shortcodes in order
- **AND** for each shortcode, SHALL generate text: `[shortcode-name attr1="value1" attr2="value2"]content[/shortcode-name]`
- **AND** SHALL handle self-closing shortcodes: `[shortcode-name /]`
- **AND** SHALL properly escape attribute values containing quotes

#### Scenario: Preserve non-shortcode content
- **WHEN** the page contains HTML content between shortcodes
- **THEN** that content SHALL be preserved in its original position
- **AND** SHALL NOT be modified during serialization

#### Scenario: Handle nested shortcodes correctly
- **WHEN** a shortcode contains other shortcodes as content
- **THEN** the nested shortcodes SHALL be serialized recursively
- **AND** the nesting structure SHALL be preserved accurately

### Requirement: PostMessage Communication
The visual builder SHALL use the PostMessage API for secure bidirectional communication between parent window and iframe.

#### Scenario: Parent sends update command to iframe
- **WHEN** user edits a shortcode attribute in sidebar
- **THEN** parent SHALL send PostMessage: `{ type: 'update-shortcode', payload: { id, attributes } }`
- **AND** SHALL include origin verification
- **WHEN** iframe receives the message
- **THEN** SHALL verify message origin matches expected domain
- **AND** SHALL update the specified shortcode block

#### Scenario: Iframe sends edit request to parent
- **WHEN** user clicks edit icon in iframe
- **THEN** iframe SHALL send PostMessage: `{ type: 'edit-shortcode', payload: { id } }`
- **WHEN** parent receives the message
- **THEN** SHALL verify message origin
- **AND** SHALL open edit panel for specified shortcode

#### Scenario: Iframe signals ready state
- **WHEN** iframe finishes loading
- **THEN** iframe SHALL send PostMessage: `{ type: 'ready' }`
- **WHEN** parent receives ready message
- **THEN** SHALL inject edit icon overlays into iframe
- **AND** SHALL enable sidebar controls

### Requirement: Data Attribute Injection
The shortcode rendering system SHALL inject data attributes into rendered HTML to enable visual builder identification and editing.

#### Scenario: Shortcodes have identifying data attributes
- **WHEN** a page is rendered with `?visual_builder=1` query parameter
- **THEN** each shortcode block SHALL have data attributes injected:
  - `data-shortcode-id`: Unique identifier for this shortcode instance
  - `data-shortcode-name`: The shortcode type name
  - `data-shortcode-position`: Zero-based position in content

#### Scenario: Data attributes use existing filter hook
- **WHEN** shortcode rendering occurs
- **THEN** the `shortcode_content_compiled` filter SHALL be used to inject attributes
- **AND** injection SHALL happen at priority 9999 (after other filters)
- **AND** attributes SHALL be added to the top-level HTML element of rendered shortcode

#### Scenario: Data attributes work with all shortcode types
- **WHEN** any registered shortcode renders
- **THEN** data attributes SHALL be injected regardless of shortcode type
- **AND** SHALL work for shortcodes with complex HTML structure
- **AND** SHALL work for shortcodes with minimal HTML output

### Requirement: Auto-Save Draft
The visual builder SHALL automatically save draft changes every 30 seconds to prevent data loss.

#### Scenario: Auto-save timer starts on first edit
- **WHEN** user makes the first change to any shortcode
- **THEN** an auto-save timer SHALL start with 30-second interval
- **AND** a subtle indicator SHALL show "Auto-saving..." when save occurs

#### Scenario: Auto-save persists to draft version
- **WHEN** auto-save triggers
- **THEN** current state SHALL be serialized
- **AND** SHALL be saved to a draft version (not published content)
- **AND** SHALL NOT trigger full page save workflow

#### Scenario: Draft is restored on reload
- **WHEN** user closes visual builder without saving
- **AND** reopens visual builder for same page
- **THEN** SHALL detect existing draft
- **AND** SHALL prompt: "Restore unsaved changes from [timestamp]?"
- **WHEN** user confirms
- **THEN** SHALL load draft state instead of published content

#### Scenario: Explicit save clears draft
- **WHEN** user clicks "Save" button and save succeeds
- **THEN** the draft version SHALL be deleted
- **AND** published content SHALL match saved state

### Requirement: Device Preview Modes
The visual builder SHALL provide device preview modes to test responsive layouts.

#### Scenario: Device mode selector displays options
- **WHEN** visual builder is open
- **THEN** header bar SHALL display device mode selector with options: Desktop, Tablet, Mobile
- **AND** Desktop SHALL be selected by default

#### Scenario: Tablet mode adjusts iframe width
- **WHEN** user selects "Tablet" mode
- **THEN** iframe SHALL adjust to 768px width
- **AND** SHALL be centered with gray background on sides
- **AND** shortcode edit icons SHALL remain functional

#### Scenario: Mobile mode adjusts iframe width
- **WHEN** user selects "Mobile" mode
- **THEN** iframe SHALL adjust to 375px width
- **AND** SHALL be centered with gray background on sides
- **AND** shortcode edit icons SHALL scale appropriately for smaller size

#### Scenario: Device mode persists during editing
- **WHEN** user switches device mode
- **AND** continues editing shortcodes
- **THEN** preview updates SHALL maintain selected device width
- **AND** user can switch between modes at any time without losing changes

### Requirement: Error Handling and Recovery
The visual builder SHALL handle errors gracefully and provide clear feedback to users.

#### Scenario: Network error during save
- **WHEN** user clicks "Save" button
- **AND** network request fails (timeout, 500 error, etc.)
- **THEN** an error notification SHALL appear: "Failed to save changes. Please try again."
- **AND** changes SHALL remain in visual builder state (not lost)
- **AND** user can retry save operation

#### Scenario: Invalid shortcode syntax
- **WHEN** serialization produces invalid shortcode syntax
- **THEN** validation SHALL detect the error before save
- **AND** an error notification SHALL appear identifying the problematic shortcode
- **AND** user SHALL be able to fix the issue or revert that shortcode

#### Scenario: Iframe fails to load
- **WHEN** iframe encounters error (404, 500, CORS issue)
- **THEN** iframe area SHALL display error message
- **AND** message SHALL include: "Unable to load preview. Please check your connection and try again."
- **AND** a "Reload Preview" button SHALL be provided

#### Scenario: JavaScript error in visual builder
- **WHEN** an unhandled JavaScript error occurs
- **THEN** the error SHALL be logged to browser console
- **AND** error SHALL be caught by global error handler
- **AND** user SHALL see notification: "An error occurred. Please refresh the page."
- **AND** auto-save draft SHALL preserve work up to point of error
