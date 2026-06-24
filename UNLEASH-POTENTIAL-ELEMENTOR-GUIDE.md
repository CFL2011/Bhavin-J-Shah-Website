# Unleash Your Potential — Page Spec (Elementor + Live Site)

**Two things are delivered:**
1. The page is **already rebuilt and live** in your actual website (`index.html` → "Unleash Your Potential"). Every number, date, price, video link and gallery image is editable from the **Admin Panel** (no code).
2. This document = the **Elementor build spec** in case you ever rebuild it in WordPress/Elementor.

---

## GLOBAL DESIGN SYSTEM

| Token | Value |
|-------|-------|
| White background | `#FFFFFF` |
| Deep navy (dark sections) | `#0B1B33` → gradient to `#10294d` |
| Royal blue accent | `#1E4FD8` |
| Gold accent (CTA / highlights) | `#D4AF37` |
| Soft grey sections | `#F8FAFC` |
| Body text | `#475569` |
| Heading text | `#0B1B33` |
| Font | Inter (Headings 700–800, body 400) |
| Container max width | 1200 px |
| Section padding | Desktop 80px top/bottom · Tablet 60px · Mobile 48px |
| Card radius | 16px · Card border `#E2E8F0` · subtle shadow |

**CTA button style:** Gold background `#D4AF37`, navy text `#0B1B33`, weight 800, padding 14px 28px, radius 12px, hover darken to `#C19B2E`. Secondary button = transparent with gold border + gold text.

**Heading sizes (Desktop / Tablet / Mobile):**
- H1: 48 / 38 / 30 px
- H2: 32 / 28 / 24 px
- H3: 20 / 18 / 18 px
- Body: 16 / 15 / 14 px

---

## SEO

- **SEO Title:** Unleash Your Potential | Personal & Professional Growth Workshop — Bhavin J. Shah
- **Meta Description:** Join Unleash Your Potential, a premium personal and professional growth workshop by Life & Business Coach Bhavin J. Shah. Become more aware, confident, and action-oriented. Inquire now.
- **URL Slug:** `/unleash-your-potential`
- **H1:** Unleash Your Potential (one only)
- **H2:** The Workshop · Join Us To · Next Batch Details · Why Unleash Your Hidden Potential? · Who Is This Workshop For? · Past Batches Gallery · Know the Coach · Testimonials · Sharing by Participants · What People Say · Money-Back Guarantee · Most Frequent Questions and Answers · Ready to Unleash Your Potential?
- **H3:** For Business Owners · For Homemakers · For Individuals (inside "Who Is This For")

---

## SECTION-BY-SECTION (Elementor)

For every section: use a **Section** (or Flexbox **Container**), inner **Columns** as noted, full-width stretch off (boxed 1200px) unless stated.

### 1. Hero
- **Structure:** 1 Section, 2 columns (60% / 40%). On mobile → stack, text first.
- **Widgets:** Heading (badge as small Heading or Button-style text), Heading (H1), Heading (subtitle), Text Editor, Button x2, Image/Video Placeholder.
- **Content:** Badge "A Workshop for Personal & Professional Growth" · H1 "Unleash Your Potential" · "For anyone above the age of 22" · intro paragraph · Buttons: **Inquire Now** (gold) + **Know the Schedule** (gold outline).
- **Media:** Right column = clean placeholder box for workshop image/video. Add link later.
- **Background:** Navy gradient, white text.
- **Padding:** 96px / 72px / 64px.
- **Mobile:** Columns stack, buttons full-width, H1 30px.

### 2. Quote / Core Message
- **Structure:** 1 Section, 1 centered column (max 720px).
- **Widgets:** Icon (quote), Heading (blockquote), Text (author "Barry Gallagher"), Text Editor (2 paragraphs).
- **Background:** White. **Padding:** 64px.

### 3. Workshop Introduction
- **Structure:** 1 Section, 1 column (max 800px). **Widgets:** Heading "The Workshop" + Text Editor (3 paragraphs).
- **Background:** Soft grey `#F8FAFC`.

### 4. Join Us To
- **Structure:** 1 Section, **Icon List** widget OR 2-column grid of small cards.
- **Widgets:** Heading + Icon List (gold check icons) with the 7 lines.
- **Background:** White.

### 5. Next Batch & Investment
- **Structure:** 1 Section, navy background, inner card container.
- **Widgets:** Heading, Text, an **Icon Box / Text grid** (2 cols) for Program / Mode / Sessions / Batch Start / Investment, Button x2.
- **Editable fields:** Batch date, Total sessions, Investment+GST — keep these as separate text widgets so you can edit easily.
- **Background:** Navy gradient. **CTA:** Inquire Now (gold) + Know the Schedule (outline).
- ⚠️ **Verify investment amount, GST %, schedule link, and batch date before publishing.**

### 6. Why Unleash Your Hidden Potential?
- **Structure:** 1 Section, 2-column **Icon List** of 7 benefits (gold bolt icon). **Background:** White.

### 7. Who Is This Workshop For?
- **Structure:** 1 Section, **3 columns** (Inner Section / 3 containers).
- **Widgets per column:** Icon Box (icon + H3) + Icon List.
  - Col 1 H3 **For Business Owners** (8 points)
  - Col 2 H3 **For Homemakers** (7 points)
  - Col 3 H3 **For Individuals** (8 points)
- **Background:** Soft grey, white cards. **CTA below:** Inquire Now.
- **Mobile:** 3 cards stack vertically.

### 8. Past Batches Gallery
- **Structure:** 1 Section. Use Elementor **Gallery** widget OR 6 Image placeholder boxes in a 3-col grid + 1 full-width video placeholder.
- **Media placeholders:** Gallery Image 1–4, Group Photo, Activity Photo, + Highlight Video link box.
- **Background:** White. **Mobile:** 2-col grid.

### 9. Know the Coach
- **Structure:** 1 Section, 2 columns (40% image / 60% text).
- **Widgets:** Image placeholder (profile), Heading "Know the Coach", Text (name + role), Text Editor (3 paragraphs), + 2 small placeholders (speaking image, intro video).
- **Background:** Soft grey.
- ⚠️ **Verify all numbers before publishing.**

### 10. Impact Numbers
- **Structure:** 1 Section, **4 columns** of Counter widgets (or Heading+Text).
- **Numbers (editable):** Lives Touched 3,70,000+ · Success Stories 1000+ · Years 14+ · Monthly Reach 5000+.
- **Background:** Navy, gold numbers. **Mobile:** 2x2 grid.

### 11. Testimonials
- **Structure:** 1 Section. 3 **video placeholder** boxes (row 1) + 2 **screenshot placeholders** (row 2).
- **Important:** Leave placeholders empty — **do NOT add fake names/testimonials.** Add real video links / screenshots later.
- **Background:** White.

### 12. Sharing by Participants
- **Structure:** 1 Section, 3-column video placeholders. **Background:** Soft grey.

### 13. What People Say
- **Structure:** 1 Section, 3 columns: Review Screenshot · Video Review · Written Testimonial (all placeholders). **Background:** White.

### 14. Money-Back Guarantee
- **Structure:** 1 Section, 1 centered card (max 720px).
- **Widgets:** Icon + Heading + Text Editor + Icon List (6 T&C points).
- **Background:** Soft grey, white card with gold border.
- ⚠️ **Verify legal terms and refund conditions before publishing.**

### 15. FAQ
- **Structure:** 1 Section, 1 column (max 720px).
- **Widget:** Elementor **Accordion** (or Toggle) widget with all **10 Q&A** items. First item can stay closed.
- **Background:** White.

### 16. Final CTA
- **Structure:** 1 Section, centered.
- **Widgets:** Heading "Ready to Unleash Your Potential?" + Text + **3 Buttons**: Inquire Now (gold) · Know the Schedule (outline) · Talk to Our Team (white outline).
- **Background:** Navy gradient.

---

## EDITABLE ITEMS (on the LIVE site, via Admin Panel)

| What | Where in Admin |
|------|----------------|
| Batch date, Total sessions, Investment + GST | **Website Texts** section |
| Impact numbers (Lives / Stories / Years / Reach) | **Website Texts** section |
| Schedule link, all video links, coach intro video | **Video & Social Links** section |
| Gallery images, coach photos, testimonial/review screenshots | **Photos** section (each shows recommended pixel size) |

---

## FINAL REVIEW CHECKLIST (before publishing)

- [ ] Investment amount **₹84,500** correct? GST **18%** correct?
- [ ] Batch date **26th June 2026, Friday** correct?
- [ ] Total **18 Sessions** correct?
- [ ] Schedule link added (button currently points to contact until set)?
- [ ] Impact numbers verified: 3,70,000+ / 1000+ / 14+ / 5000+
- [ ] Refund T&C reviewed by you / legal?
- [ ] No fake testimonials or fake names anywhere ✓ (placeholders only)
- [ ] All gallery/coach/testimonial placeholders replaced with real media (or left clean)
- [ ] All "Inquire Now" / "Talk to Our Team" buttons go to your contact form
- [ ] Tested on mobile, tablet, desktop
- [ ] SEO title, meta description, slug set
- [ ] Page loads fast, no broken links
