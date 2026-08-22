# Guru — WordPress theme

Custom theme converted from the hand-built static site. The design is
unchanged; the content the client updates is now editable in the admin.

## Install

1. WP admin → **Appearance → Themes → Add New → Upload Theme**
2. Choose `guru-theme.zip` → **Install Now** → **Activate**
3. **Settings → Permalinks** → click *Save* once (registers `/work/` URLs)

No plugins required.

## What the client can edit

| Admin location | Controls |
|---|---|
| **Showcase** | Work items — title, client, cover image, video, categories |
| **Solutions** | The 9 solutions (homepage accordion + Solutions page) |
| **Client logos** | Logo ticker / logo wall — title + logo image |
| **Posts** | Blog |
| **Enquiries** | Every contact-form submission, archived |
| **Appearance → Customize → Guru site settings** | Email, phone, WhatsApp, social links, hero video |
| **Appearance → Menus** | Primary nav (optional — falls back to defaults) |

## Adding a showcase item

**Showcase → Add new**

- **Title** — project name, e.g. `Hennessy × NBA Season 2`
- **Showcase details → Client name** — shown under the title
- **Showcase details → Video** — *Choose video* picks from the Media Library.
  Leave empty for an image-only project.
- **Cover image** (right sidebar) — used when there is no video
- **Categories** — drives the filter buttons on the Work page

## Contact form

Submissions are emailed to the address in *Customize → Guru site settings →
Enquiry email* **and** saved under **Enquiries**, so nothing is lost if the
host's mail delivery fails. A hidden honeypot field blocks basic spam bots.

If the host does not send mail reliably, install an SMTP plugin
(e.g. WP Mail SMTP) — no theme change needed.

## Notes

- Hero video ships with the theme; override it in the Customizer to serve a
  file from the Media Library instead.
- Styles live in one `style.css` (the static site had the CSS duplicated
  across six pages).
