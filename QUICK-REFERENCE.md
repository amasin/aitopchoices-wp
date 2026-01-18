# Quick Reference - AI Top Choices Plugin

## 🚀 Immediate Next Steps

### 1. Activate Plugin (1 min)
```bash
# WordPress Admin:
Plugins → Activate "AI Top Choices - AI Tools"

# Or WP-CLI:
wp plugin activate aitc-ai-tools
```

### 2. Flush Permalinks (30 sec)
```bash
Settings → Permalinks → Save Changes
```

### 3. Import Sample Data (2 min)
```bash
AI Tools → Import CSV → Upload sample-import.csv
```

### 4. Verify (1 min)
- Visit: `/ai-tools/` (archive)
- Visit: `/ai-tools/chatgpt/` (single tool)
- Check schema: View page source, search for `application/ld+json`

---

## 📍 Admin Locations

| Feature | Location |
|---------|----------|
| Add New Tool | AI Tools → Add New |
| View All Tools | AI Tools → All AI Tools |
| Categories | AI Tools → Categories |
| Import CSV | AI Tools → Import CSV |
| Moderate Reviews | AI Tools → Moderate Reviews |
| Settings | AI Tools → Settings |

---

## 🎯 Common Tasks

### Create a Tool Manually
1. AI Tools → Add New
2. Fill title, excerpt, content
3. Set featured image
4. Fill "Pricing Information" meta box
5. Fill "Editorial Review" meta box
6. Assign categories/tags
7. Publish

### Import Tools via CSV
1. Prepare CSV with required columns
2. AI Tools → Import CSV
3. Upload file
4. Check "Update existing" if needed
5. Import

### Moderate Reviews
1. AI Tools → Moderate Reviews
2. Click status tab (Pending/Approved/Spam)
3. Click action button (Approve/Spam/Delete)

### Toggle Schema Output
1. AI Tools → Settings
2. Check/uncheck "Enable JSON-LD schema output"
3. Save Settings

---

## 📝 CSV Template

**Minimum required:**
```csv
title,excerpt,content
"ChatGPT","AI assistant","Full description here"
```

**Full example:**
```csv
title,excerpt,content,official_url,pricing_model,price_type,price_single_amount,billing_unit,editor_rating_value,category_slug
"Tool Name","Short desc","Long desc",https://example.com,freemium,single,19.99,month,4.5,chatbots
```

**Complex (with tiers):**
```csv
title,pricing_tiers_json,editor_pros,editor_cons
"Tool","[{""name"":""Free"",""amount"":0},{""name"":""Pro"",""amount"":20}]","Pro 1|Pro 2|Pro 3","Con 1|Con 2"
```

---

## 🔧 Pricing Configurations

### Free Tool
- pricing_model: `free`
- price_type: `none`
- has_free_plan: `1`

### Single Price
- price_type: `single`
- price_single_amount: `19.99`
- billing_unit: `month`

### Price Range
- price_type: `range`
- price_range_low: `10.00`
- price_range_high: `50.00`
- billing_unit: `month`

### Tiered Pricing
- price_type: `tiers`
- pricing_tiers_json: `[{"name":"Basic","amount":10},{"name":"Pro","amount":30}]`

### Enterprise (Contact Sales)
- pricing_model: `enterprise`
- price_type: `none`

---

## 🎨 Template Files

**Override in theme:**
```
wp-content/themes/your-theme/
└── aitc-ai-tools/
    ├── single-ai_tool.php
    ├── archive-ai_tool.php
    └── taxonomy-ai_tool_category.php
```

**Plugin originals:**
```
templates/
```

---

## 🔍 URL Structure

| Page Type | URL Example |
|-----------|-------------|
| Archive | `/ai-tools/` |
| Single Tool | `/ai-tools/chatgpt/` |
| Category | `/ai-tool-category/chatbots/` |
| Use Case | `/ai-use-case/content-creation/` |
| Platform | `/ai-platform/web/` |

---

## 📊 Database

**Custom table:**
```sql
wp_aitc_tool_ratings
```

**Post meta keys (prefix: _):**
```
official_url
pricing_model
price_type
editor_rating_value
... (17 total)
```

**Options:**
```
aitc_ai_tools_schema_enabled
```

---

## 🛡️ Security Notes

**Rate Limiting:**
- Guest reviews: Max 3 per hour (per IP)

**Auto-Approval:**
- Logged-in users: Auto-approved
- Guest users: Pending moderation

**Spam Protection:**
- Honeypot field (hidden "website")
- IP hash tracking
- User agent hash tracking

---

## ⚡ Performance

**Caching:**
- Rating summaries cached 24h (transients)
- Auto-bust on review status change

**Indexes:**
- post_id, user_id, status, created_at

**Frontend:**
- CSS/JS loaded only on tool pages
- No external dependencies

---

## 🔗 Schema Types

**Single Tool:**
```json
{
  "@type": "SoftwareApplication",
  "offers": { ... },
  "review": { "author": "AI Top Choices" },
  "aggregateRating": { ... }
}
```

**Archive:**
```json
{
  "@type": "ItemList",
  "itemListElement": [ ... ]
}
```

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| 404 on /ai-tools/ | Flush permalinks |
| Reviews not showing | Check moderation status |
| Schema missing | Check Settings → Enable schema |
| Import fails | Check CSV encoding (UTF-8) |
| Table missing | Deactivate + reactivate plugin |

---

## 📦 Sample Data Included

**File:** `sample-import.csv`

**Contains:** 10 AI tools
- ChatGPT
- Midjourney
- Grammarly
- Notion AI
- Copy.ai
- GitHub Copilot
- Jasper
- Runway ML
- Descript
- Perplexity AI

**Import:** AI Tools → Import CSV

---

## 📖 Documentation

| File | Purpose |
|------|---------|
| [README.md](README.md) | Plugin features |
| [SETUP.md](SETUP.md) | Installation guide |
| [TESTING-CHECKLIST.md](TESTING-CHECKLIST.md) | 150+ test cases |
| [IMPLEMENTATION-SUMMARY.md](IMPLEMENTATION-SUMMARY.md) | Complete overview |
| [QUICK-REFERENCE.md](QUICK-REFERENCE.md) | This file |

---

## ✅ Pre-Launch Checklist

- [ ] Plugin activated
- [ ] Permalinks flushed
- [ ] Sample data imported (or removed)
- [ ] First tool created manually
- [ ] Featured images set
- [ ] Review submitted and moderated
- [ ] Schema verified in source
- [ ] Archive page styled
- [ ] Mobile responsive checked
- [ ] SEO meta descriptions set
- [ ] Google Search Console connected
- [ ] Backup configured

---

## 🎯 Production URLs to Test

1. **Archive:** `https://yourdomain.com/ai-tools/`
2. **Single:** `https://yourdomain.com/ai-tools/chatgpt/`
3. **Category:** `https://yourdomain.com/ai-tool-category/chatbots/`
4. **Schema Test:** [Google Rich Results Test](https://search.google.com/test/rich-results)

---

## 🚦 Status Indicators

**Settings Page (AI Tools → Settings):**

✅ **Database Table: ✓ Installed** (green) = Good
❌ **Database Table: ✗ Not found** (red) = Reactivate plugin

**Review Moderation Tabs:**
- **Pending (5)** = 5 reviews awaiting approval
- **Approved (20)** = 20 reviews live on site
- **Spam (2)** = 2 reviews marked as spam

---

## 🔄 Update Workflow

### To Update Tool Pricing:
1. Edit tool in admin
2. Update "Pricing Information" fields
3. Click Update
4. Frontend auto-updates

### To Update Reviews:
1. AI Tools → Moderate Reviews
2. Change status (Approve/Spam)
3. Rating cache auto-clears
4. Frontend refreshes on next load

### To Re-import CSV:
1. Check "Update existing tools with matching titles"
2. Import updated CSV
3. Existing tools update, new tools add

---

## 💡 Pro Tips

1. **Use Tiers for Complex Pricing:** JSON format supports unlimited tiers
2. **Set Featured Images:** Improves visual appeal + schema
3. **Write Good Excerpts:** Used in meta descriptions + archive cards
4. **Enable Free Trial:** Boosts conversions, shows in schema
5. **Moderate Reviews Daily:** Keep content fresh and trustworthy
6. **Use Categories Wisely:** Improves navigation + SEO
7. **Test Schema Regularly:** Google Rich Results Test
8. **Cache Rating Summaries:** Already done (24h transients)
9. **Override Templates:** Customize in theme for unique design
10. **Monitor Pending Reviews:** AI Tools → Settings shows count

---

## 📞 Need Help?

1. Check [TESTING-CHECKLIST.md](TESTING-CHECKLIST.md) for detailed walkthroughs
2. Review [SETUP.md](SETUP.md) troubleshooting section
3. Examine [README.md](README.md) for technical details

---

**Version:** 1.0.0 | **Status:** Production Ready ✅
