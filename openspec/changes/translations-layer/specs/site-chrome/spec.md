# site-chrome

## ADDED Requirements

### Requirement: Language toggle drives live translation
The header EN/ES toggle SHALL, on translation-active pages (see `translation-layer`), trigger live translation via the GTranslate bridge in addition to recording the `rgvdsa_lang` preference. On pages where translation is not active it SHALL remain an interactive preference recorder whose ES label tooltip states the current scope ("Español — disponible en la página de inicio"). All responsive header instances SHALL stay in sync through the shared preference singleton.

#### Scenario: Toggle on home
- **WHEN** a visitor flips the toggle to ES on the front page
- **THEN** the page translates and `rgvdsa_lang=es` is recorded

#### Scenario: Toggle on inner page
- **WHEN** a visitor flips the toggle to ES on a non-translated page
- **THEN** only the preference is recorded, content stays English, and the tooltip explains ES is available on the home page

### Requirement: Chrome excluded from machine translation
The site header and footer island mounts, and any chrome content portaled outside them (header dropdown menus), SHALL carry `notranslate` so Google Translate never mutates Vue-managed DOM. Chrome labels remain English in v1; future Spanish chrome SHALL come from native strings driven by the language preference, not DOM machine translation.

#### Scenario: Chrome untouched in ES
- **WHEN** the front page is translated to ES
- **THEN** header nav labels, the Join CTA, dropdown menu items, and footer text render exactly as in English with no translation wrapper elements inside island DOM
