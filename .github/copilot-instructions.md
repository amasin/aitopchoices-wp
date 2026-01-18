# AI Top Choices Plugin - AI Coding Agent Instructions

## Project Overview
This is a WordPress plugin (`aitc-ai-tools`) that manages an AI tools catalogue with structured pricing, user ratings, editorial reviews, and JSON-LD schema markup. The plugin extends WordPress with a custom post type (`ai_tool`), five taxonomies, and a custom ratings database table.

## Architecture & Data Model

### Core Components
- **Post Type**: `ai_tool` at slug `/ai-tools/` with REST API support
- **Taxonomies**: 
  - `ai_tool_category` (hierarchical, 14 pre-seeded child categories under parent "AI Tools")
  - `ai_use_case`, `ai_platform`, `ai_pricing_model`, `ai_billing_unit` (all tag-based)
- **Custom Table**: `wp_aitc_tool_ratings` stores user ratings with IP/user-agent hashing, moderation status, and timestamps

### Pricing Model
All pricing stored canonically in **USD** as post meta with underscore prefix (`_pricing_model`, `_price_single_amount`, etc.). Supports:
- Models: free, freemium, paid, usage, one_time, enterprise, open_source
- Price types: none, single, range, tiers
- Tiered pricing stored as JSON array in `_pricing_tiers_json`: `[{"name":"Starter","amount":19,"currency":"USD","unit":"month","notes":"..."}]`

### Editorial & User Reviews
- **Editorial**: Post meta fields (`_editor_rating_value`, `_editor_review_summary`, `_editor_pros/cons/features`)
- **User Ratings**: DB table with auto-approve for logged-in users, pending moderation for guests
- **Rate Limiting**: 3 reviews per hour per IP; honeypot spam protection
- **Caching**: Rating summaries cached with 24-hour transient

## Plugin Initialization Flow

1. **Activation** (`aitc_ai_tools_activate`): Registers post types, taxonomies, seeds default terms, creates ratings table, flushes rewrites
2. **Init Hook (Priority 10)**: `AITC_Post_Types::register()` + `AITC_Taxonomies::register()` (must run early for `$wp_rewrite`)
3. **Init Hook (Priority 20)**: All component initialization—meta boxes, ratings, schema, templates, admin pages

This two-phase approach ensures post types exist before dependent features initialize.

## File Organization & Key Patterns

### Class Locations & Responsibilities
- [includes/class-post-types.php](includes/class-post-types.php): Static `register()` method registers `ai_tool` CPT
- [includes/class-taxonomies.php](includes/class-taxonomies.php): Taxonomy registration + `seed_default_terms()` (pre-populates 14 categories, 7 pricing models, 6 billing units)
- [includes/class-meta-boxes.php](includes/class-meta-boxes.php): Admin pricing & editorial meta boxes with dynamic field visibility (e.g., price fields show only for paid models)
- [includes/class-ratings.php](includes/class-ratings.php): Database setup, AJAX rating submission, cached summaries, honeypot/rate-limiting logic
- [includes/class-schema.php](includes/class-schema.php): JSON-LD SoftwareApplication schema output; disableable via `aitc_ai_tools_schema_enabled` option
- [includes/class-templates.php](includes/class-templates.php): Template loader—uses plugin templates if they exist, falls back to theme
- [admin/class-csv-importer.php](admin/class-csv-importer.php): Bulk CSV import with pipe/comma-separated taxonomy assignment
- [admin/class-settings.php](admin/class-settings.php): Schema toggle and plugin settings
- [admin/class-ratings-admin.php](admin/class-ratings-admin.php): Review moderation interface

### Code Patterns

**Class Structure**: All features use static methods with static `init()` hooks to register actions/filters.

**Post Meta**: All meta uses underscore prefix (`_field_name`) and `get_post_meta()`/`update_post_meta()`.

**Nonce Security**: All forms use `wp_nonce_field()` + `check_admin_referer()`. AJAX uses `wp_create_nonce()` + client-side verification.

**Template Fallback**: [class-templates.php](includes/class-templates.php) looks for templates in theme first, then plugin `templates/` directory.

**Validation**: CSV importer validates required fields; meta boxes sanitize URLs and JSON; ratings validate 1–5 star range and check user authorization.

## Development Workflows

### CSV Import
- Place CSV file in plugin root (e.g., `sample-import.csv`)
- Navigate to **AI Tools → Import CSV** and upload
- Columns: title, excerpt, content, official_url, pricing_page_url, pricing_model, price_type, price_single_amount, price_range_low/high, billing_unit, has_free_plan/trial, trial_days, pricing_tiers_json, editor_rating_value, editor_review_summary, editor_pros/cons/features, category_slug, use_case, platform, ai_pricing_model, ai_billing_unit
- Taxonomy fields accept comma-separated term slugs; features/pros/cons accept pipe-separated values

### Adding a New Field to Tools
1. Define post meta key with underscore prefix (e.g., `_my_field`)
2. Add to [class-meta-boxes.php](includes/class-meta-boxes.php) render method and save hook
3. Retrieve in templates via `get_post_meta($post_id, '_my_field', true)`
4. Update CSV importer to map new column if needed

### Extending Taxonomies
1. Add registration function to [class-taxonomies.php](includes/class-taxonomies.php)
2. Call in `register()` method
3. If pre-seeded terms needed, add to `seed_default_terms()` using `wp_insert_term()`
4. Update CSV importer to accept new taxonomy slugs

## Integration Points & Considerations

- **REST API**: Post type has `show_in_rest: true`; taxonomies expose via REST
- **Schema Output**: Disabled by default if RankMath/Yoast detected (manual toggle in Settings)
- **AJAX**: Ratings form uses `wp_ajax_aitc_submit_rating` (for logged-in) + `wp_ajax_nopriv_aitc_submit_rating` (for guests)
- **Database**: Custom table created on activation; table name prefixed with `wp_` and blog prefix
- **Assets**: Admin CSS/JS enqueued only on post edit pages; frontend CSS/JS on single tool pages

## Testing Checklist Reference
See [TESTING-CHECKLIST.md](TESTING-CHECKLIST.md) for comprehensive test cases covering CPT, taxonomies, pricing UI, editorial reviews, user ratings, CSV import, schema output, and admin pages.

## Constants & Configuration
```php
AITC_AI_TOOLS_VERSION      // Plugin version (1.0.0)
AITC_AI_TOOLS_FILE         // Main plugin file path
AITC_AI_TOOLS_PATH         // Plugin directory path
AITC_AI_TOOLS_URL          // Plugin directory URL
aitc_ai_tools_schema_enabled  // Option: enable/disable JSON-LD output
```

## Common Tasks
- **Debug ratings**: Check `wp_aitc_tool_ratings` table; verify transient cache with `get_transient('aitc_rating_summary_' . $post_id)`
- **Fix schema issues**: Verify `aitc_ai_tools_schema_enabled` option; check schema structure in browser DevTools
- **Manual rating submission**: See [class-ratings.php](includes/class-ratings.php) `ajax_submit_rating()` method
- **Theme template override**: Copy `templates/single-ai_tool.php` to theme and customize
