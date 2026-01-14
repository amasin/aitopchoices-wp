# AI Top Choices - AI Tools Plugin

A comprehensive WordPress plugin for managing an AI Tools catalogue with ratings, reviews, structured pricing, editorial reviews, and JSON-LD schema markup.

## Features

### Custom Post Type
- **AI Tools** (`ai_tool`) - Custom post type at `/ai-tools/`
- Full WordPress editor support with excerpts and featured images
- REST API enabled for headless implementations

### Taxonomies

#### Hierarchical Category (`ai_tool_category`)
Pre-seeded with the following structure:
- AI Tools (parent)
  - Writing
  - Image & Design
  - Video
  - Coding
  - Marketing
  - Productivity
  - Audio & Voice
  - Chatbots
  - Business
  - Data & Analytics
  - Education
  - Automation & Agents
  - Research Platforms
  - Aggregators

#### Tag-based Taxonomies
- **Use Cases** (`ai_use_case`) - Flexible tagging for use cases
- **Platforms** (`ai_platform`) - Platform availability
- **Pricing Models** (`ai_pricing_model`) - Pre-seeded: Free, Freemium, Paid, Usage-based, One-time, Enterprise, Open-source
- **Billing Units** (`ai_billing_unit`) - Pre-seeded: month, year, one_time, seat_month, seat_year, usage

### Structured Pricing Data
All pricing stored canonically in USD with the following fields:
- Pricing model (enum)
- Price type (none, single, range, tiers)
- Single price amount
- Price range (low/high)
- Billing unit
- Free plan/trial availability
- Trial days
- Pricing page URL
- Official URL
- Tiered pricing (JSON format)

**Tier Format:**
```json
[
  {
    "name": "Starter",
    "amount": 19,
    "currency": "USD",
    "unit": "month",
    "notes": "Up to 10 users"
  }
]
```

### Editorial Review System
- Editor rating (1.0-5.0)
- Review summary
- Pros (one per line)
- Cons (one per line)
- Key features (one per line)

### User Ratings & Reviews
- **Database table:** `wp_aitc_tool_ratings`
- One rating per logged-in user per tool (updates existing)
- Guest reviews allowed (default to pending moderation)
- Star rating (1-5) + optional title/text
- Honeypot spam protection
- Rate limiting (3 reviews per hour per IP)
- Admin moderation interface
- Cached rating summaries (transients)
- AJAX submission

### JSON-LD Schema
Automatic structured data output for SEO:

**Single Tool Page:**
- SoftwareApplication schema
- Pricing offers (single/range/tiers/enterprise)
- Editorial review (when rating exists)
- Aggregate user rating (when ≥5 approved reviews)
- Publisher: AI Top Choices

**Archive/Taxonomy Pages:**
- ItemList schema (up to 25 items)

**Settings:**
- Option to disable schema output (avoid duplication with SEO plugins)

### Templates
Plugin ships with theme-compatible templates:
- `templates/single-ai_tool.php` - Single tool page
- `templates/archive-ai_tool.php` - Archive listing
- `templates/taxonomy-ai_tool_category.php` - Category archive

Templates can be overridden in theme: `{theme}/aitc-ai-tools/single-ai_tool.php`

### CSV Import
Admin interface at **AI Tools → Import CSV**

**Supported columns:**
```
title,excerpt,content,official_url,pricing_page_url,pricing_model,price_type,
price_single_amount,price_range_low,price_range_high,billing_unit,has_free_plan,
has_free_trial,trial_days,pricing_tiers_json,editor_rating_value,
editor_review_summary,editor_features,editor_pros,editor_cons,category_slug,
use_case,platform,ai_pricing_model,ai_billing_unit
```

**Notes:**
- `editor_features`, `editor_pros`, `editor_cons`: Use pipe-separator (`|`) for multiple items
- `category_slug`, `use_case`, etc.: Comma-separated term slugs
- `has_free_plan`, `has_free_trial`: Use `1` or `0`
- Option to update existing tools by matching title

### Admin Features
- **Meta Boxes:** Pricing and Editorial Review sections
- **Moderation Page:** AI Tools → Moderate Reviews (approve/spam/delete)
- **Settings Page:** Enable/disable schema output
- **Import Page:** Bulk CSV import

## Installation

1. Upload the `aitc-ai-tools` folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. Plugin will automatically:
   - Create custom post type and taxonomies
   - Seed default category terms
   - Create ratings database table
   - Flush rewrite rules

## File Structure

```
aitc-ai-tools/
├── aitc-ai-tools.php          # Main plugin file
├── README.md                   # This file
├── includes/
│   ├── class-post-types.php    # CPT registration
│   ├── class-taxonomies.php    # Taxonomy registration & seeding
│   ├── class-meta-boxes.php    # Admin meta boxes
│   ├── class-ratings.php       # Ratings system
│   ├── class-schema.php        # JSON-LD output
│   └── class-templates.php     # Template loader
├── admin/
│   ├── class-settings.php      # Settings page
│   ├── class-csv-importer.php  # CSV import
│   └── class-ratings-admin.php # Review moderation
├── templates/
│   ├── single-ai_tool.php
│   ├── archive-ai_tool.php
│   └── taxonomy-ai_tool_category.php
└── assets/
    ├── css/
    │   ├── admin.css
    │   └── ratings.css
    └── js/
        ├── admin.js
        └── ratings.js
```

## Database Schema

### wp_aitc_tool_ratings
```sql
CREATE TABLE wp_aitc_tool_ratings (
  id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id BIGINT(20) UNSIGNED NOT NULL,
  user_id BIGINT(20) UNSIGNED DEFAULT NULL,
  rating TINYINT(1) UNSIGNED NOT NULL,
  review_title VARCHAR(255) DEFAULT NULL,
  review_text TEXT DEFAULT NULL,
  status ENUM('pending','approved','spam') DEFAULT 'pending',
  ip_hash CHAR(64) NOT NULL,
  user_agent_hash CHAR(64) NOT NULL,
  created_at DATETIME NOT NULL,
  KEY post_id (post_id),
  KEY user_id (user_id),
  KEY status (status),
  KEY created_at (created_at)
);
```

## Security Features

- Nonce verification on all forms
- Capability checks (`edit_posts`, `manage_options`)
- Input sanitization and output escaping
- SQL injection prevention (prepared statements)
- Honeypot anti-spam field
- Rate limiting (IP-based)
- XSS protection

## Performance Optimizations

- Database indexes on ratings table
- Transient caching for rating summaries (24 hours)
- Cache busting on status changes
- Efficient SQL queries with JOINs

## Compatibility

- WordPress 5.8+
- PHP 7.4+
- Theme-agnostic (tested with GeneratePress)
- Works with REST API
- No external PHP dependencies

## Hooks & Filters

The plugin is designed to be extensible. Key action hooks:

- `aitc_before_tool_content` - Before tool content
- `aitc_after_tool_content` - After tool content
- Template system allows theme overrides

## Support

For issues and feature requests, visit the plugin repository.

## License

GPL v2 or later

## Changelog

### 1.0.0
- Initial release
- Custom post type and taxonomies
- Pricing meta fields
- Editorial review system
- User ratings and reviews
- JSON-LD schema output
- CSV importer
- Admin moderation interface
- Frontend templates
