# AI Top Choices Plugin - Testing Checklist

## 1. Plugin Activation

### In WordPress Admin:
- [ ] Navigate to **Plugins → Installed Plugins**
- [ ] Click **Activate** on "AI Top Choices - AI Tools"
- [ ] Verify no PHP errors appear
- [ ] Check that "AI Tools" menu appears in admin sidebar (with superhero icon)

### Verify Database:
- [ ] Go to **AI Tools → Settings**
- [ ] Confirm "Database Table: ✓ Installed" shows green checkmark
- [ ] Verify table `wp_aitc_tool_ratings` exists in database

### Verify Taxonomies Were Seeded:
- [ ] Go to **AI Tools → Categories**
- [ ] Confirm "AI Tools" parent category exists
- [ ] Confirm 14 child categories exist (Writing, Image & Design, Video, etc.)
- [ ] Go to **AI Tools → Pricing Models**
- [ ] Confirm 7 pricing models exist (Free, Freemium, Paid, etc.)
- [ ] Go to **AI Tools → Billing Units**
- [ ] Confirm 6 billing units exist (month, year, one_time, etc.)

### Verify Permalinks:
- [ ] Go to **Settings → Permalinks**
- [ ] Click **Save Changes** (to flush rewrites)
- [ ] Navigate to `/ai-tools/` on frontend
- [ ] Verify archive page loads without 404

---

## 2. Creating an AI Tool (Manual Entry)

### Basic Information:
- [ ] Go to **AI Tools → Add New**
- [ ] Enter title: "ChatGPT"
- [ ] Add excerpt: "AI-powered conversational assistant"
- [ ] Add content: Full description with formatting
- [ ] Set featured image (upload a logo)
- [ ] Click **Publish**
- [ ] Verify post publishes successfully

### Pricing Information Meta Box:
- [ ] Scroll to "Pricing Information" meta box
- [ ] Set **Official URL**: `https://openai.com/chatgpt`
- [ ] Set **Pricing Page URL**: `https://openai.com/pricing`
- [ ] Set **Pricing Model**: Freemium
- [ ] Set **Price Type**: Tiers
- [ ] In **Pricing Tiers (JSON)**, paste:
  ```json
  [
    {"name":"Free","amount":0,"currency":"USD","unit":"month","notes":"Limited features"},
    {"name":"Plus","amount":20,"currency":"USD","unit":"month","notes":"GPT-4 access"},
    {"name":"Team","amount":25,"currency":"USD","unit":"seat_month","notes":"Collaboration tools"}
  ]
  ```
- [ ] Set **Billing Unit**: month
- [ ] Check **Has Free Plan**
- [ ] Check **Has Free Trial**
- [ ] Set **Trial Days**: 7
- [ ] Click **Update**
- [ ] Verify fields save correctly (reload page to confirm)

### Editorial Review Meta Box:
- [ ] Scroll to "Editorial Review" meta box
- [ ] Set **Editor Rating**: 4.5
- [ ] Set **Review Summary**: "ChatGPT revolutionizes AI conversation with natural language understanding."
- [ ] Set **Pros** (one per line):
  ```
  Natural conversation flow
  Wide range of capabilities
  Free tier available
  ```
- [ ] Set **Cons** (one per line):
  ```
  Can produce incorrect information
  Limited real-time knowledge
  API rate limits
  ```
- [ ] Set **Key Features** (one per line):
  ```
  Natural language processing
  Code generation
  Multiple languages
  Conversational memory
  ```
- [ ] Click **Update**
- [ ] Verify fields save correctly

### Assign Taxonomies:
- [ ] In the right sidebar, under **AI Tool Categories**, select "Chatbots"
- [ ] Under **Use Cases**, add: "Content Creation, Customer Support"
- [ ] Under **Platforms**, add: "Web, iOS, Android"
- [ ] Under **Pricing Models**, select "Freemium"
- [ ] Click **Update**

---

## 3. Frontend Single Tool Page

### Access Tool Page:
- [ ] Click **View Post** or visit `/ai-tools/chatgpt/`
- [ ] Verify page loads without errors

### Verify Content Display:
- [ ] Confirm tool title displays correctly
- [ ] Confirm featured image (logo) displays
- [ ] Confirm excerpt displays
- [ ] Confirm full content displays
- [ ] Confirm "Visit Official Website" button links to `https://openai.com/chatgpt`

### Verify Pricing Section:
- [ ] Confirm "Pricing" section displays
- [ ] Confirm "Pricing Model: Freemium" displays
- [ ] Confirm all 3 pricing tiers display:
  - Free: $0.00 USD / month
  - Plus: $20.00 USD / month
  - Team: $25.00 USD / seat_month
- [ ] Confirm "✓ Free plan available" displays
- [ ] Confirm "✓ Free trial available (7 days)" displays
- [ ] Confirm "View full pricing details →" link points to pricing page

### Verify Editorial Review:
- [ ] Confirm "Our Review" section displays
- [ ] Confirm "Editor Rating: 4.5/5.0" with stars displays
- [ ] Confirm review summary text displays
- [ ] Confirm "Key Features" section with 4 items displays
- [ ] Confirm "Pros" section with 3 items (with ✓) displays
- [ ] Confirm "Cons" section with 3 items (with ✗) displays

### Verify User Reviews Section:
- [ ] Confirm "User Reviews" heading displays
- [ ] Confirm "No reviews yet" message displays (initially)
- [ ] Confirm "Submit Your Review" form displays
- [ ] Verify form has:
  - Star rating input (5 clickable stars)
  - Review title field
  - Review text textarea
  - Submit button

### Verify Categories Footer:
- [ ] Confirm "Categories: Chatbots" displays at bottom
- [ ] Confirm category is a clickable link

---

## 4. User Review Submission

### As Logged-In User:
- [ ] Log in to WordPress
- [ ] Visit the ChatGPT tool page
- [ ] Click on 4 stars in the rating form
- [ ] Enter **Review Title**: "Great for productivity"
- [ ] Enter **Review Text**: "I use ChatGPT daily for writing emails and brainstorming ideas."
- [ ] Click **Submit Review**
- [ ] Verify success message: "Your rating has been submitted!"
- [ ] Wait 2 seconds for page reload
- [ ] Confirm your review appears in the reviews list with:
  - Your username
  - 4 stars
  - Review title
  - Review text
  - Today's date

### Update Existing Review (Logged-In):
- [ ] Scroll back to form
- [ ] Change rating to 5 stars
- [ ] Change title to "Absolutely essential"
- [ ] Click **Submit Review**
- [ ] Verify message: "Your rating has been updated!"
- [ ] Confirm review updated (not duplicated)

### As Guest User:
- [ ] Log out of WordPress
- [ ] Visit the ChatGPT tool page
- [ ] Submit a review with 3 stars
- [ ] Enter title: "Useful but has limitations"
- [ ] Click **Submit Review**
- [ ] Verify message: "Your rating has been submitted and is awaiting moderation."
- [ ] Confirm review does NOT appear in list (pending approval)

### Test Spam Protection:
- [ ] Open browser developer tools
- [ ] In the review form, find hidden field `name="website"`
- [ ] Enter any value in the website field
- [ ] Try to submit review
- [ ] Verify error: "Spam detected."

### Test Rate Limiting:
- [ ] As guest, submit 3 reviews quickly
- [ ] Try to submit a 4th review within the same hour
- [ ] Verify error: "You are submitting reviews too quickly..."

---

## 5. Admin Review Moderation

### Access Moderation Page:
- [ ] Log back in as admin
- [ ] Go to **AI Tools → Moderate Reviews**
- [ ] Verify page loads

### Review Pending Tab:
- [ ] Click **Pending** tab
- [ ] Confirm guest review appears
- [ ] Verify review shows:
  - Tool name (ChatGPT)
  - 3 stars
  - Review title and text preview
  - "Guest" as author
  - Date submitted
  - Approve, Spam, Delete buttons

### Approve a Review:
- [ ] Click **Approve** button
- [ ] Confirm redirect to moderation page
- [ ] Click **Approved** tab
- [ ] Verify review moved to Approved section
- [ ] Visit ChatGPT tool page on frontend
- [ ] Confirm guest review now appears in reviews list

### Mark Review as Spam:
- [ ] Return to **AI Tools → Moderate Reviews**
- [ ] Click **Approved** tab
- [ ] Click **Spam** on the guest review
- [ ] Click **Spam** tab
- [ ] Verify review appears in Spam section
- [ ] Visit ChatGPT tool page
- [ ] Confirm review no longer appears

### Delete a Review:
- [ ] Go back to moderation page
- [ ] Click **Delete** on spam review
- [ ] Confirm deletion in browser alert
- [ ] Verify review is permanently deleted

---

## 6. Rating Summary and Aggregate

### Create Multiple Reviews:
- [ ] Create 4 more test user accounts or use guest submissions
- [ ] Submit 4 more reviews (all approved) to reach 5+ total
- [ ] Ensure ratings vary (e.g., 5, 4, 5, 3, 4)

### Verify Rating Summary:
- [ ] Visit ChatGPT tool page
- [ ] Confirm "User Reviews" section shows:
  - Average rating (e.g., "4.2")
  - Star display matches average
  - Review count (e.g., "5 reviews")

### Verify JSON-LD Schema:
- [ ] On ChatGPT tool page, view page source
- [ ] Search for `<script type="application/ld+json">`
- [ ] Verify schema includes:
  - `"@type": "SoftwareApplication"`
  - `"name": "ChatGPT"`
  - `"url": "https://yoursite.com/ai-tools/chatgpt/"`
  - `"operatingSystem": "Web"`
  - `"applicationCategory": "Chatbots"`
  - `"offers"` section with AggregateOffer and tiers
  - `"review"` section with editor rating
  - `"aggregateRating"` section (only if ≥5 approved reviews)
- [ ] Verify aggregateRating shows:
  - `"ratingValue"` matches frontend average
  - `"reviewCount"` matches frontend count

---

## 7. Archive and Taxonomy Pages

### Archive Page (/ai-tools/):
- [ ] Navigate to `/ai-tools/` on frontend
- [ ] Verify page displays with "AI Tools" heading
- [ ] Confirm ChatGPT tool card displays with:
  - Featured image
  - Title (linked)
  - Excerpt
  - Editor rating with stars
  - User rating with stars and count
  - Pricing badge ("Freemium")
  - "Learn More" button
  - "Visit Site" button

### Category Archive:
- [ ] Click "Chatbots" category link
- [ ] Verify URL is `/ai-tools/chatbots/`
- [ ] Verify page heading shows "Chatbots"
- [ ] Confirm ChatGPT tool displays

### Verify ItemList Schema:
- [ ] View source on archive or category page
- [ ] Search for `"@type": "ItemList"`
- [ ] Verify schema includes:
  - `"itemListElement"` array
  - ListItem with ChatGPT URL and name
  - `"position": 1`

---

## 8. CSV Import

### Prepare Test CSV:
- [ ] Create file `test-tools.csv` with content:
  ```csv
  title,excerpt,content,official_url,pricing_page_url,pricing_model,price_type,price_single_amount,billing_unit,has_free_plan,editor_rating_value,editor_review_summary,editor_features,editor_pros,editor_cons,category_slug,use_case,platform,ai_pricing_model
  Midjourney,AI image generation from text,"Midjourney creates stunning AI-generated artwork from text descriptions.",https://midjourney.com,https://midjourney.com/pricing,paid,tiers,,,0,4.7,"Exceptional AI art generation with unique artistic style.","Text-to-image generation|Multiple art styles|High resolution output","Stunning visual quality|Active community|Regular improvements","Requires Discord|No free tier|Learning curve",image-design,"Image Generation,Creative Work",Discord,Paid
  Grammarly,Writing assistant,"AI-powered writing assistant that helps with grammar, spelling, and style.",https://grammarly.com,https://grammarly.com/pricing,freemium,range,12,30,month,1,4.3,"Comprehensive writing assistant with excellent grammar checking.","Grammar checking|Style suggestions|Plagiarism detection","Easy to use|Browser extension|Real-time feedback","Premium can be expensive|Some suggestions miss context",writing,"Writing,Editing",Web,Freemium
  ```

### Import CSV:
- [ ] Go to **AI Tools → Import CSV**
- [ ] Verify import page displays with CSV format instructions
- [ ] Upload `test-tools.csv`
- [ ] Check **Update existing tools with matching titles**
- [ ] Click **Import CSV**
- [ ] Verify success message: "Successfully imported 2 tools!"

### Verify Imported Tools:
- [ ] Go to **AI Tools → All AI Tools**
- [ ] Confirm Midjourney and Grammarly appear in list
- [ ] Edit Midjourney:
  - Verify title, excerpt, content populated
  - Verify official URL set
  - Verify pricing model = "paid"
  - Verify price type = "tiers"
  - Verify editor rating = 4.7
  - Verify editor features populated (3 items)
  - Verify category "Image & Design" assigned
  - Verify use case "Image Generation, Creative Work" assigned
- [ ] Edit Grammarly:
  - Verify price type = "range"
  - Verify price range low = 12, high = 30
  - Verify billing unit = "month"
  - Verify has_free_plan checked
  - Verify category "Writing" assigned

### Test Update on Re-import:
- [ ] Modify CSV: Change Grammarly excerpt
- [ ] Re-import CSV with "Update existing" checked
- [ ] Verify Grammarly excerpt updated
- [ ] Verify no duplicate Grammarly created

---

## 9. Plugin Settings

### Access Settings:
- [ ] Go to **AI Tools → Settings**
- [ ] Verify page loads

### Verify Plugin Information:
- [ ] Confirm "Version: 1.0.0" displays
- [ ] Confirm "Database Table: ✓ Installed" (green)
- [ ] Confirm "Total AI Tools" shows correct count (3+)
- [ ] Confirm "Total Reviews" shows correct count
- [ ] Confirm "Pending Reviews" shows count

### Test Schema Toggle:
- [ ] Uncheck "Enable JSON-LD schema output"
- [ ] Click **Save Settings**
- [ ] Verify success message
- [ ] Visit ChatGPT tool page
- [ ] View source
- [ ] Confirm NO `<script type="application/ld+json">` present
- [ ] Return to Settings
- [ ] Re-enable schema
- [ ] Click **Save Settings**
- [ ] Verify schema appears again on frontend

---

## 10. Edge Cases and Security

### Test Empty Fields:
- [ ] Create new AI Tool without filling pricing/editorial fields
- [ ] Verify tool saves without errors
- [ ] Verify frontend displays gracefully (no PHP notices)

### Test Price Type Switching:
- [ ] Create tool with price_type = "single", amount = 50
- [ ] Save and verify
- [ ] Change price_type to "range"
- [ ] Set range low = 20, high = 100
- [ ] Save and verify frontend shows range, not single price

### Test Invalid JSON in Tiers:
- [ ] Set pricing_tiers_json to invalid JSON: `{broken`
- [ ] Save tool
- [ ] Visit frontend
- [ ] Verify no fatal errors (tiers section simply doesn't display)

### Test XSS Protection:
- [ ] Submit review with title: `<script>alert('XSS')</script>`
- [ ] Approve review
- [ ] Visit tool page
- [ ] Verify script tags are escaped (no alert appears)
- [ ] Verify review text displays as plain text

### Test SQL Injection:
- [ ] Submit review with text: `'); DROP TABLE wp_aitc_tool_ratings; --`
- [ ] Verify review saves safely
- [ ] Verify database table still exists
- [ ] Verify no SQL errors

### Test Review Rate Limit Reset:
- [ ] Wait 1 hour after hitting rate limit
- [ ] Try submitting another guest review
- [ ] Verify submission succeeds

---

## 11. Performance Tests

### Check Query Performance:
- [ ] Install Query Monitor plugin
- [ ] Visit ChatGPT tool page
- [ ] Check queries in Query Monitor
- [ ] Verify rating summary uses cached transient (no DB query after first load)
- [ ] Verify no N+1 query issues

### Test Cache Busting:
- [ ] Note current rating average on ChatGPT page
- [ ] Submit and approve a new 1-star review
- [ ] Reload ChatGPT page
- [ ] Verify rating average updated correctly
- [ ] Check transient was busted

---

## 12. Theme Compatibility

### Test with Default Theme:
- [ ] Switch to Twenty Twenty-Four theme
- [ ] Visit `/ai-tools/` archive
- [ ] Verify layout adapts to theme
- [ ] Visit ChatGPT tool page
- [ ] Verify all sections display correctly

### Test Template Override:
- [ ] In your theme, create folder: `aitc-ai-tools`
- [ ] Copy `single-ai_tool.php` from plugin to theme folder
- [ ] Modify template (e.g., add custom heading)
- [ ] Visit ChatGPT tool page
- [ ] Verify your custom template is used

---

## 13. REST API (Bonus)

### Test REST Endpoints:
- [ ] Visit `/wp-json/wp/v2/ai_tool`
- [ ] Verify tools list returns JSON
- [ ] Verify meta fields NOT exposed (privacy)
- [ ] Visit `/wp-json/wp/v2/ai_tool/{id}`
- [ ] Verify single tool returns JSON

---

## Summary

Once all items above are checked:
- ✅ Plugin is ready for production
- ✅ All core functionality tested
- ✅ Security validated
- ✅ Performance optimized
- ✅ SEO schema working
- ✅ User experience confirmed

**Total Test Cases:** 150+

---

## Quick Smoke Test (5 minutes)

For rapid testing after updates:

1. ✅ Activate plugin (no errors)
2. ✅ Create one AI Tool with pricing and editorial review
3. ✅ View tool page (all sections display)
4. ✅ Submit logged-in review (appears immediately)
5. ✅ Submit guest review (goes to pending)
6. ✅ Approve guest review in moderation
7. ✅ Check schema in page source
8. ✅ Import 2-tool CSV
9. ✅ View `/ai-tools/` archive
10. ✅ Toggle schema setting

---

## Troubleshooting

**404 on /ai-tools/:**
- Go to Settings → Permalinks and click Save Changes

**Database table not created:**
- Deactivate and reactivate plugin
- Check PHP error log for SQL errors

**Reviews not appearing:**
- Check moderation page status (must be "approved")
- Clear browser cache
- Check transient cache was busted

**Schema not appearing:**
- Check Settings → Schema is enabled
- Verify you're viewing frontend (not admin)
- View source, not inspector (schema is in raw HTML)

**Import fails:**
- Check CSV encoding (UTF-8)
- Verify column headers match exactly
- Check for extra commas or quotes

---

## Test Completion Sign-off

- [ ] All core features tested and passing
- [ ] Security tests passed
- [ ] Performance acceptable
- [ ] Documentation complete
- [ ] Ready for production

Tested by: ________________
Date: ________________
Environment: WordPress _____ / PHP _____
