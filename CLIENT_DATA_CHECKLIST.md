# Real Data to Collect from the Client

**Dynamic:** entered in the CMS and used by the website.
**Static:** supplied to the developer for changes to page files, images, or configuration.
Some details appear in both; collect them once and update every occurrence.

## 1. Dynamic data — CMS-managed

### Global Settings

- Real primary and secondary phone numbers, including country code.
- Active WhatsApp number, including country code.
- Public contact email address.
- Full main clinic address: street/area, city, district, and country.
- Exact main clinic map location.
- Opening and closing times for every day, including closed days and breaks.
- Public URLs for Privacy Policy, Terms and Conditions, and Accessibility information.

These settings update supported contact links, footer contact details, open/closed status, and legal links. Some other contact blocks remain static; see section 3.

### Branches — collect for each branch

- Real branch name and main/secondary designation.
- Full address.
- Phone number.
- Opening days and hours.
- Exact Google Maps location link.
- Branches to publish and their display order.
- Branch email, if used; this can be stored in the CMS but is not currently shown in the generated branch list.

Confirm whether Butwal and Bhairahawa are actual branches. Their existing map coordinates are approximate.

### Doctors — collect for each doctor

- Full professional name.
- Specialty.
- Qualifications.
- NMC registration number.
- Short professional biography.
- Consultation days, times, and branch/location.
- Current availability.
- Approved portrait photo and image description.
- Doctors to publish and their display order.

CMS records populate the homepage and Doctors page when published records are available.

### Services and specialties — collect for each service

- Actual service name.
- Short patient-facing description.
- Tests, treatments, or procedures to list.
- Approved service photo and image description.
- Services to publish and their display order.

CMS records populate homepage service cards, the Specialties page, and appointment service options. Footer service links remain static.

Confirm which existing services are offered: specialist consultation, advanced diagnostics, laboratory services, physiotherapy, obstetrics and gynecology, and nephrology/kidney care.

### Blog — if retained

- Actual article titles and full text.
- Categories.
- Author and reviewer names, where applicable.
- Publication and review dates.
- Approved article images and descriptions.
- Articles to publish.

### Contact form topics

- Actual enquiry topics.
- Which topics should be available publicly.
- Responsible staff email address for each topic.

Topics populate the public form. Staff email addresses can be stored in the CMS; this does not establish that automatic email delivery is connected.

### Search and sharing information

- Approved search titles and short descriptions for Home, About, Specialties, Doctors, Blog, and Contact.
- Page-specific social sharing titles, descriptions, and images, where needed.

## 2. Static data — developer/file changes

### Branding and website configuration

- Final website domain.
- Official short display name for the header.
- Copyright owner name and location.
- Approved short clinic description for the footer.
- Original/high-quality official logo.
- Browser/favicon and Apple touch icon, or approved logo to use for them.
- Default image for sharing website links on social media.

The logo, icons, and default sharing image use fixed file paths. The domain is configured separately from CMS content.

### Homepage text

- Approved headline and introduction.
- Accurate description of the clinic's facilities and in-house testing.
- Verified reasons to choose the clinic.
- Approved appointment instructions.
- Actual services to include in the fixed footer service links.

### About page text

- Approved clinic introduction.
- Actual mission, vision, and values.
- Real clinic history.

### Fixed photos

- Homepage/banner background photo.
- Actual clinic team photo.
- About-page clinic/reception photo.
- Other approved clinic exterior/interior photos.
- Image descriptions and permission to publish identifiable people.

Doctor, service, and article images belong in the dynamic section above.

### Patient and contact information

- Documents or items patients should bring.
- Test preparation instructions or the correct preparation contact.
- Report collection method and expected timing.
- Accepted payment methods.
- Walk-in availability and restrictions.
- Branch-specific service availability and booking instructions to publish.
- Appointment confirmation process.
- Public holiday arrangements.
- Expected response time for enquiries.
- Approved emergency notice.
- Actual emergency number and operating hours, only if the clinic provides this service.

The public response-time text and emergency notices are currently static, even though the CMS has fields for some of this information.

### Policy documents and newsletter

- Approved Privacy Policy document.
- Approved Terms and Conditions document.
- Approved Accessibility information.
- Privacy/accessibility contact.
- Confirmed health article review process and approved wording for review claims.
- Newsletter service details and setup contact, if subscriptions will be enabled.

Policy URLs can be set in Global Settings, but policy content must be supplied separately. The newsletter currently displays success without saving subscriptions.

### Social profiles

- Official Facebook, Instagram, and LinkedIn URLs, where used.

These are saved through Global Settings and used in clinic structured data. Visible social-profile links are not currently rendered by the shared settings script.

## 3. Shared details — collect once, update dynamic and static copies

| Real data to collect | Dynamic use | Static copies that also need updating |
|---|---|---|
| Official clinic name | CMS setting and clinic structured data | Header/footer branding, page copy, copyright, and some article wording |
| Main reception number | Header/footer phone links, quick-call links, homepage number, and clinic structured data | Mobile menu Call links on all public pages; About/Doctors/Specialties call buttons; homepage appointment-help link; Contact reception link |
| WhatsApp number | Floating button and bottom mobile link | Contact page's “Prefer to talk?” WhatsApp link |
| Public email | Footer contact details and clinic structured data | Contact information card and homepage Visit section |
| Opening hours | Footer, open/closed status, clinic structured data; branch hours in branch lists | Contact information card and homepage Visit section |
| Branch details and map pins | Branch lists, selected map, and selected contact branch fields | Built-in branch lists/maps used when CMS data is unavailable |
| Doctor details | Published doctor cards | Built-in “Dr. [Name]” cards and sample schedules used when CMS records are empty or unavailable |
| Service details | Published service cards, listings, and appointment options | Built-in service content used as fallback, footer service links, and descriptive page copy |

**Current values to replace or confirm:**

- Main phone: `+977 71-000000`.
- Secondary phone: `+977 98-0000000`.
- WhatsApp: `+9779800000000`.
- Email: `info@nmcbutwal.com.np`.
- Hours: Sunday-Friday 8 AM-8 PM; Saturday 9 AM-2 PM.
- Main address: Traffic Chowk, Butwal, Rupandehi, Nepal.
- Branches: Butwal and Bhairahawa.

## 4. Approval to collect

- Name of the person authorized to approve the website content.
- Confirmation that supplied contact details, services, doctor information, text, and images are accurate and approved for publication.
