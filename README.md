# AI Top Choices – AI Tools Catalogue

This repository contains the **WordPress codebase** for **AI Top Choices**, a curated AI tools discovery and comparison platform.

The core objective of this project is to build a **scalable, SEO-first AI tools catalogue** that supports:
- Human curation
- Programmatic SEO
- Structured data (schema)
- Long-term monetization

---

## 📌 Project Goals

- Create a structured catalogue of AI tools (10,000+ scalable)
- Avoid auto-scraped, low-quality directories
- Emphasize **editorial curation and trust**
- Build a foundation for:
  - AI tool comparisons
  - Best-of lists
  - Aggregator reviews
  - Use-case driven discovery

---

## 🧠 Architecture Overview

This repository intentionally contains **only user-controlled WordPress code**.

### WordPress Plugin: AI Top Choices - AI Tools

Located at: `wp-content/plugins/aitc-ai-tools/`

**Core Features:**
- Custom Post Type: `ai_tool` at `/ai-tools/`
- Hierarchical taxonomies with pre-seeded category tree
- Structured pricing data (USD canonical, single/range/tiers)
- Editorial review system (rating, pros/cons, features)
- User ratings & reviews with moderation
- JSON-LD schema output (SoftwareApplication + ItemList)
- CSV bulk import
- Frontend templates (theme-compatible)

**Key Capabilities:**
- ✅ Store pricing in multiple formats (single/range/tiered)
- ✅ Editor and user ratings with aggregate display
- ✅ Schema markup for rich snippets (Google)
- ✅ Rate limiting & spam protection
- ✅ Cached rating summaries for performance
- ✅ Admin moderation workflow
- ✅ Bulk CSV import with term auto-creation

**Documentation:**
- Plugin README: [`wp-content/plugins/aitc-ai-tools/README.md`](wp-content/plugins/aitc-ai-tools/README.md)
- Testing Guide: [`wp-content/plugins/aitc-ai-tools/TESTING-CHECKLIST.md`](wp-content/plugins/aitc-ai-tools/TESTING-CHECKLIST.md)
- Sample CSV: [`wp-content/plugins/aitc-ai-tools/sample-import.csv`](wp-content/plugins/aitc-ai-tools/sample-import.csv)
