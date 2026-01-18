# Setup Guide - AI Top Choices WordPress Plugin

## Quick Start (5 minutes)

### 1. Activate the Plugin

```bash
# If you have WP-CLI installed:
wp plugin activate aitc-ai-tools

# Or via WordPress Admin:
# Plugins → Installed Plugins → Activate "AI Top Choices - AI Tools"
```

**What happens on activation:**
- Custom post type `ai_tool` registered
- 5 taxonomies created (category, use_case, platform, pricing_model, billing_unit)
- Default terms seeded (14 categories, 7 pricing models, 6 billing units)
- Database table `wp_aitc_tool_ratings` created
- Rewrite rules flushed

### 2. Flush Permalinks

```bash
# WP-CLI:
wp rewrite flush

# Or in WordPress Admin:
# Settings → Permalinks → Click "Save Changes"
```

### 3. Verify Installation

Go to **AI Tools → Settings** in WordPress admin and confirm:
- ✓ Database Table: Installed (green checkmark)
- Version shows 1.0.0

### 4. Import Sample Data

```bash
# Option 1: Use the provided sample CSV
# Go to: AI Tools → Import CSV
# Upload: sample-import.csv
# Check: "Update existing tools with matching titles"
# Click: Import CSV

# Option 2: WP-CLI (if available)
wp aitc import-csv sample-import.csv
```

This imports 10 pre-configured AI tools (ChatGPT, Midjourney, Grammarly, etc.)

### 5. View Frontend

Visit these URLs:
- Archive: `https://yoursite.com/ai-tools/`
- Single: `https://yoursite.com/ai-tools/chatgpt/`
- Category: `https://yoursite.com/ai-tool-category/chatbots/`

---

## Manual Tool Creation (WordPress Admin)

### Create Your First AI Tool

1. Go to **AI Tools → Add New**

2. **Basic Info:**
   - Title: "ChatGPT"
   - Excerpt: "AI-powered conversational assistant"
   - Content: Full description (supports rich text)
   - Featured Image: Upload logo

3. **Pricing Information** (meta box):
   - Official URL: `https://chat.openai.com`
   - Pricing Page: `https://openai.com/pricing`
   - Pricing Model: Freemium
   - Price Type: Tiers
   - Pricing Tiers (JSON):
     ```json
     [
       {"name":"Free","amount":0,"currency":"USD","unit":"month","notes":"GPT-3.5"},
       {"name":"Plus","amount":20,"currency":"USD","unit":"month","notes":"GPT-4"}
     ]
     ```
   - Billing Unit: month
   - ☑ Has Free Plan
   - ☑ Has Free Trial
   - Trial Days: 7

4. **Editorial Review** (meta box):
   - Editor Rating: 4.8
   - Summary: "ChatGPT revolutionizes AI conversation..."
   - Pros (one per line):
     ```
     Natural conversation
     Free tier available
     Constantly improving
     ```
   - Cons (one per line):
     ```
     Can hallucinate
     No real-time data
     Rate limits
     ```
   - Features (one per line):
     ```
     Natural language processing
     Code generation
     Multi-language support
     ```

5. **Taxonomies** (sidebar):
   - Category: Chatbots
   - Use Cases: Content Creation, Customer Support
   - Platforms: Web, iOS, Android
   - Pricing Models: Freemium

6. Click **Publish**

---

## CSV Import Format

### Column Reference

| Column | Type | Required | Example |
|--------|------|----------|---------|
| `title` | text | ✓ | "ChatGPT" |
| `excerpt` | text | | "AI assistant" |
| `content` | HTML | | Full description |
| `official_url` | URL | | https://openai.com |
| `pricing_page_url` | URL | | https://openai.com/pricing |
| `pricing_model` | enum | | free\|freemium\|paid\|usage\|one_time\|enterprise\|open_source |
| `price_type` | enum | | none\|single\|range\|tiers |
| `price_single_amount` | float | | 19.99 |
| `price_range_low` | float | | 10.00 |
| `price_range_high` | float | | 50.00 |
| `billing_unit` | enum | | month\|year\|one_time\|seat_month\|seat_year\|usage |
| `has_free_plan` | bool | | 1 or 0 |
| `has_free_trial` | bool | | 1 or 0 |
| `trial_days` | int | | 7 |
| `pricing_tiers_json` | JSON | | See below |
| `editor_rating_value` | float | | 4.5 |
| `editor_review_summary` | text | | Review text |
| `editor_features` | pipe-sep | | "Feature 1\|Feature 2" |
| `editor_pros` | pipe-sep | | "Pro 1\|Pro 2" |
| `editor_cons` | pipe-sep | | "Con 1\|Con 2" |
| `category_slug` | comma-sep | | "chatbots,writing" |
| `use_case` | comma-sep | | "content-creation,coding" |
| `platform` | comma-sep | | "web,ios,android" |
| `ai_pricing_model` | comma-sep | | "freemium" |
| `ai_billing_unit` | comma-sep | | "month" |

### JSON Tier Format

```json
[
  {
    "name": "Starter",
    "amount": 19,
    "currency": "USD",
    "unit": "month",
    "notes": "Up to 10 users"
  },
  {
    "name": "Pro",
    "amount": 49,
    "currency": "USD",
    "unit": "month",
    "notes": "Unlimited users"
  }
]
```

**Important:**
- Use double quotes in JSON (not single quotes)
- Escape quotes in CSV: `"{""name"":""Starter""}"`
- Or use single quotes in CSV, double in JSON: `'{"name":"Starter"}'`

---

## Admin Workflow

### Review Moderation

1. Go to **AI Tools → Moderate Reviews**
2. Click **Pending** tab
3. Review guest submissions
4. Click **Approve** to publish or **Spam** to hide
5. Approved reviews appear on frontend immediately
6. Rating cache auto-updates

### Settings

Go to **AI Tools → Settings** to:
- Toggle JSON-LD schema output (disable if using RankMath)
- View plugin stats (total tools, reviews, pending)
- Check database table status

---

## Frontend Customization

### Override Templates

1. In your theme, create folder: `aitc-ai-tools/`
2. Copy template from plugin: `templates/single-ai_tool.php`
3. Paste to: `wp-content/themes/your-theme/aitc-ai-tools/single-ai_tool.php`
4. Customize as needed
5. WordPress automatically uses your version

### Add Custom CSS

```css
/* Customize tool cards */
.aitc-tool-card {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Customize pricing section */
.aitc-pricing-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

/* Customize star ratings */
.rating-stars {
  color: #FFD700; /* Gold */
  font-size: 20px;
}
```

---

## Performance Optimization

### Enable Object Caching

```php
// wp-config.php
define('WP_CACHE', true);

// Install Redis or Memcached for production
// Rating summaries use transients (24h cache)
```

### Optimize Database

```bash
# Add indexes (already included in table schema)
# Verify with:
wp db query "SHOW INDEX FROM wp_aitc_tool_ratings"
```

### CDN for Assets

Configure your CDN to cache:
- `/assets/css/`
- `/assets/js/`

---

## SEO Configuration

### Schema Output

**Default:** Enabled

The plugin outputs JSON-LD schema for:
- **Single tools:** SoftwareApplication with offers, reviews, ratings
- **Archives:** ItemList with up to 25 items

**Disable if using SEO plugin:**
Go to **AI Tools → Settings** and uncheck "Enable JSON-LD schema output"

### Meta Descriptions

Use your SEO plugin (RankMath, Yoast) to set:
- Tool page meta descriptions (pulls from excerpt by default)
- Archive meta description
- OG images (uses featured image)

---

## Troubleshooting

### 404 on /ai-tools/

**Fix:** Flush permalinks
- Go to **Settings → Permalinks**
- Click **Save Changes**
- Visit `/ai-tools/` again

### Reviews Not Appearing

**Check:**
1. Review status is "approved" (go to Moderate Reviews)
2. Browser cache cleared
3. No caching plugin blocking updates

### Schema Not in Source

**Check:**
1. **AI Tools → Settings** → Schema enabled
2. Viewing frontend (not admin preview)
3. Using "View Source" (not Inspect Element)
4. No SEO plugin overriding

### CSV Import Fails

**Common fixes:**
- Use UTF-8 encoding
- Check for extra commas in cells
- Escape quotes properly in JSON
- Verify column headers match exactly (case-sensitive)

### Database Table Missing

**Fix:** Reactivate plugin
```bash
wp plugin deactivate aitc-ai-tools
wp plugin activate aitc-ai-tools
```

---

## Production Checklist

Before going live:

- [ ] Permalinks flushed
- [ ] Sample data removed (or kept if desired)
- [ ] Schema output tested (Google Rich Results Test)
- [ ] Review moderation workflow tested
- [ ] CSV import tested with production data
- [ ] Featured images set for all tools
- [ ] SSL certificate installed
- [ ] Caching enabled
- [ ] CDN configured
- [ ] Backup system in place

---

## Next Steps

1. **Import your AI tools** via CSV or manual entry
2. **Set featured images** for visual appeal
3. **Configure SEO** meta titles and descriptions
4. **Test review submission** as logged-in and guest user
5. **Verify schema** with Google Rich Results Test
6. **Create categories pages** in main navigation
7. **Build comparison pages** linking to tools
8. **Promote to users** and collect reviews

---

## Support

- Plugin README: `README.md`
- Testing Guide: `TESTING-CHECKLIST.md`
- Sample Data: `sample-import.csv`

---

## Version

Current: **1.0.0**

Requirements:
- WordPress 5.8+
- PHP 7.4+
- MySQL 5.6+
