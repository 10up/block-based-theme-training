# Block Based Theme Training

This doc is the **curriculum outline + change log** for turning `10up-block-theme` into `fueled-movies`.

We start from the baseline scaffold and incrementally add capabilities until we've built a real Full Site Editing theme that supports custom content types (Movies/People), custom blocks, editor UX, and interactive frontend UI.

## Who this is for

WordPress developers — junior through senior — who are comfortable with PHP, JavaScript, and the WordPress ecosystem but may be new to the block editor and Full Site Editing. We assume you know your way around a theme's `functions.php` and have used hooks before, but haven't necessarily built a block or touched `theme.json`.

The tone here matches `gutenberg-best-practices`: practical, direct, and focused on the "why" behind each decision. We don't spell out every click, but we don't skip context either.

## Lesson format

Each lesson follows the same structure used across `gutenberg-best-practices`:

- **Learning Outcomes** — what you'll know after this lesson
- **Context** — what exists already, what changes
- **Key files** — where to look
- **Tasks** — hands-on work
- **Takeaways** — the most important things to remember
- **Ship it checkpoint** — observable proof that it works
- **Further reading** — links into `gutenberg-best-practices/` and the WordPress handbook

Screenshot and diagram suggestions are marked with 📷 and 📐 throughout. These should be created as part of lesson authoring.

---

## Repo landmarks

- **Starting theme**: `wp-content/themes/10up-block-theme`
- **Final theme**: `wp-content/themes/fueled-movies`
- **Content model + editor data** (CPTs, taxonomies, post meta, relationships): `wp-content/mu-plugins/10up-plugin`
- **Reference training style / exercise inspiration**: `wp-content/gutenberg-best-practices/`

---

## Complete delta: 10up-block-theme → fueled-movies

Every file difference between the two themes is documented below. Each delta maps to a lesson opportunity in the curriculum.

### Files removed from 10up-block-theme

| File | What it was | Why removed | Lesson |
|------|-------------|-------------|--------|
| `styles/surface-primary.json` | Global surface variation | Replaced with targeted per-block variations | Module 4 |
| `styles/surface-secondary.json` | Global surface variation | Replaced with targeted per-block variations | Module 4 |
| `styles/surface-tertiary.json` | Global surface variation | Replaced with targeted per-block variations | Module 4 |

### Files modified (exist in both, content differs)

#### Configuration

| File | What changed | Type | Lesson |
|------|-------------|------|--------|
| `theme.json` | Added `customTemplates` array (4 CPT templates); added 8-color palette (dark theme with yellow accents); added `settings.custom` block with semantic color tokens, scrollbar width, viewport calculations; added `dimensions.aspectRatios` (Movie Poster 2:3); added `styles.elements.button` and `styles.elements.link`; added `spacing.units`; base font size `16px`; wideSize `1200px` → `1219px`; schema `6.7` → `6.9` | Config | Modules 1, 2 |
| `package.json` | Added deps: `@wordpress/interactivity`, `@wordpress/dependency-extraction-webpack-plugin`; added `useScriptModules: true` to toolkit config | Config | Modules 0, 11 |
| `composer.json` | Namespace `TenupBlockTheme\` → `FueledMoviesTheme\`; framework `~1.3.1` → `~1.2.0` | Rebrand | Module 0 |
| `style.css` | Theme Name, URI, Author, Description, Text Domain all rebranded to Fueled Movies | Rebrand | Module 0 |
| `functions.php` | Constants `TENUP_BLOCK_THEME_*` → `FUELED_MOVIES_THEME_*`; namespace `TenupBlockTheme` → `FueledMoviesTheme` | Rebrand | Module 0 |
| `template-tags.php` | Namespace `TenupBlockTheme` → `FueledMoviesTheme` | Rebrand | Module 0 |

#### PHP source (`src/`)

| File | What changed | Type | Lesson |
|------|-------------|------|--------|
| `src/ThemeCore.php` | Constants and hooks rebranded (`tenup_block_theme_*` → `fueled_movies_theme_*`) | Rebrand | Module 0 |
| `src/Assets.php` | Added `is_array()` defensive check on `block-extensions` dependency array before `wp_enqueue_script()` | Bug fix | Module 0 |
| `src/Blocks.php` | Added `filter_featured_image_block()` — injects `view-transition-name` on `core/post-featured-image` via `render_block` filter; added `maybe_add_flex_shrink()` — adds `flex-shrink-0` class on blocks with fixed `selfStretch` layout (Gutenberg issue #53766 workaround) | New features | Modules 3, 10 |

#### Templates (`templates/`)

| File | What changed | Type | Lesson |
|------|-------------|------|--------|
| `templates/index.html` | Card layout changed from pattern reference (`tenup-theme/base-card`) to inline block markup with featured image (16:9), title, date, categories; perPage `9` → `10`; added border styling (8px radius, 1px border) | Content | Modules 1, 5 |
| `templates/single.html` | **Identical** | — | — |
| `templates/singular.html` | **Identical** | — | — |
| `templates/404.html` | **Identical** | — | — |

#### Template parts (`parts/`)

| File | What changed | Type | Lesson |
|------|-------------|------|--------|
| `parts/header.html` | Formatting only — compact single-line block markup vs multi-line | Whitespace | — |
| `parts/footer.html` | Content change — removed multi-column layout (site info, quick links, resources); simplified to copyright line (`© 2025 Fueled`) + legal navigation | Content | Module 1 |
| `parts/site-header-navigation-area.html` | **Identical** | — | — |
| `parts/site-footer-legal-navigation-area.html` | **Identical** | — | — |

#### CSS (`assets/css/`)

| File | What changed | Type | Lesson |
|------|-------------|------|--------|
| `assets/css/base/index.css` | Added `@import "html.css"` (new base import) | Import | Module 3 |
| `assets/css/base/layout.css` | Added `accent-color: var(--wp--custom--color--yellow--primary)` on `html`; added `@view-transition { navigation: auto; }` rule | New feature | Modules 2, 3 |
| `assets/css/components/index.css` | Added imports: `./header.css`, `./card.css`, `./button.css` (was empty comment only) | Import | Module 3 |
| `assets/css/utilities/index.css` | Added import: `./layout.css` | Import | Module 3 |
| `assets/css/utilities/visually-hidden.css` | Added `.is-hidden { display: none; }` utility class | New feature | Module 3 |
| `assets/css/base/align.css` | **Identical** | — | — |
| `assets/css/base/reset.css` | **Identical** | — | — |
| `assets/css/blocks/core/image.css` | **Identical** | — | — |
| `assets/css/editor-canvas-style-overrides.css` | **Identical** | — | — |
| `assets/css/editor-frame-style-overrides.css` | **Identical** | — | — |
| `assets/css/frontend.css` | **Identical** | — | — |
| `assets/css/globals/media-queries.css` | **Identical** | — | — |
| `assets/css/templates/index.css` | **Identical** | — | — |
| `assets/css/mixins/margin-trim.css` | **Identical** | — | — |
| `assets/css/mixins/visually-hidden.css` | **Identical** | — | — |

#### JavaScript (`assets/js/`)

| File | What changed | Type | Lesson |
|------|-------------|------|--------|
| `assets/js/block-extensions.js` | Was empty; now imports `./block-bindings`, `./block-filters`, `./block-plugins`, `./block-styles` | New feature | Modules 7, 8, 9 |
| `assets/js/frontend.js` | **Identical** | — | — |

### Files added in fueled-movies (not in 10up-block-theme)

#### Templates (4 new)

| File | Purpose | Lesson |
|------|---------|--------|
| `templates/archive-tenup-movie.html` | Movie archive with query loop grid | Module 1 |
| `templates/single-tenup-movie.html` | Single movie with block bindings, custom blocks, featured image | Modules 1, 8, 10 |
| `templates/archive-tenup-person.html` | Person archive with query loop grid | Module 1 |
| `templates/single-tenup-person.html` | Single person with relationship bindings, metadata | Modules 1, 8 |

#### Patterns (1 new)

| File | Purpose | Lesson |
|------|---------|--------|
| `patterns/single-movie-trailer.php` | IMDB trailer embed or placeholder based on post meta (composition pattern) | Module 5 |

#### Style variations (2 new, replacing 3 removed surface variations)

| File | Purpose | Lesson |
|------|---------|--------|
| `styles/button/secondary.json` | Secondary button: transparent bg, primary text, inset shadow, hover/focus states | Module 4 |
| `styles/group/secondary.json` | Secondary group: 10px radius, transparent bg, 32px padding | Module 4 |

#### PHP source (1 new)

| File | Purpose | Lesson |
|------|---------|--------|
| `src/BlockBindings.php` | Registers `tenup/block-bindings` source; routes 8 binding keys (`archiveLinkText`, `archiveLinkUrl`, `movieStars`, `personBorn`, `personDied`, `personMovies`, `viewerRatingLabelText`, `viewerRatingLabelUrl`); uses Content Connect for relationship queries; formats counts with K notation | Module 8 |

#### CSS (8 new files)

| File | Purpose | Lesson |
|------|---------|--------|
| `assets/css/base/html.css` | `a` and `button` transition rules (color, background-color, border-color) | Module 3 |
| `assets/css/blocks/core/group.css` | `.has-separator` modifier — dot-separated inline content using `::before` pseudo-elements | Module 9 |
| `assets/css/blocks/core/post-featured-image.css` | View transition naming for featured images | Module 3 |
| `assets/css/blocks/core/post-terms.css` | Typography overrides for taxonomy term display | Module 3 |
| `assets/css/blocks/core/separator.css` | Custom separator appearance | Module 3 |
| `assets/css/components/button.css` | Button flex alignment and gap | Module 3 |
| `assets/css/components/card.css` | Post card styling with overlay link trick (absolute-positioned `<a>` covering the card) | Module 3 |
| `assets/css/components/header.css` | Sticky header with backdrop blur, z-index management, background-nav color | Module 3 |
| `assets/css/utilities/layout.css` | `.flex-shrink-0` utility class | Module 3 |

#### JavaScript (23 new files)

| File | Purpose | Lesson |
|------|---------|--------|
| `assets/js/block-bindings/index.js` | Editor preview values for `tenup/block-bindings` source | Module 8 |
| `assets/js/block-components/DateTimePopover.js` | Shared date/time picker popover component | Module 7 |
| `assets/js/block-components/PostMeta/MovieIMDBID.js` | IMDB ID text field | Module 7 |
| `assets/js/block-components/PostMeta/MovieMPARating.js` | MPA Rating dropdown (G, PG, PG-13, R, NC-17, Unrated) | Module 7 |
| `assets/js/block-components/PostMeta/MoviePlot.js` | Plot textarea | Module 7 |
| `assets/js/block-components/PostMeta/MovieReleaseYear.js` | Release year field | Module 7 |
| `assets/js/block-components/PostMeta/MovieRuntime.js` | Runtime hours/minutes fields | Module 7 |
| `assets/js/block-components/PostMeta/MovieViewerRating.js` | Viewer rating display | Module 7 |
| `assets/js/block-components/PostMeta/MovieViewerRatingCount.js` | Rating count display | Module 7 |
| `assets/js/block-components/PostMeta/MovieTrailerID.js` | IMDB Trailer ID field | Module 7 |
| `assets/js/block-components/PostMeta/PersonBiography.js` | Biography textarea | Module 7 |
| `assets/js/block-components/PostMeta/PersonBirthplace.js` | Birthplace text field | Module 7 |
| `assets/js/block-components/PostMeta/PersonBorn.js` | Birth date picker | Module 7 |
| `assets/js/block-components/PostMeta/PersonDeathplace.js` | Deathplace text field | Module 7 |
| `assets/js/block-components/PostMeta/PersonDied.js` | Death date picker | Module 7 |
| `assets/js/block-components/PostMeta/PersonIMDBID.js` | Person IMDB ID field | Module 7 |
| `assets/js/block-filters/index.js` | Filter aggregator (imports `./group`) | Module 9 |
| `assets/js/block-filters/group.js` | Extends `core/group` with `has-separator` toggle + class name | Module 9 |
| `assets/js/block-plugins/index.js` | Plugin aggregator (imports movie + person panels) | Module 7 |
| `assets/js/block-plugins/movie-meta-fields.js` | `PluginDocumentSettingPanel` for Movie post meta (scoped to `tenup-movie`) | Module 7 |
| `assets/js/block-plugins/person-meta-fields.js` | `PluginDocumentSettingPanel` for Person post meta (scoped to `tenup-person`) | Module 7 |
| `assets/js/block-styles/index.js` | Block style registration (currently placeholder/aggregator) | Module 4 |
| `assets/js/block-variations/index.js` | Block variation registration (currently placeholder/aggregator) | Module 4 |

#### Custom blocks (6 new blocks, 30+ files)

| Block | Files | Purpose | Lesson |
|-------|-------|---------|--------|
| `blocks/dl/` | `block.json`, `markup.php`, `index.js`, `style.css`, `example.js`, `transforms.js` | Semantic `<dl>` wrapper; parent of `tenup/dl-item` | Module 10 |
| `blocks/dl-item/` | `block.json`, `markup.php`, `index.js`, `style.css`, `icon.js` | Term+description pair; parent is `tenup/dl`, children are `tenup/dt` + `tenup/dd` | Module 10 |
| `blocks/dt/` | `block.json`, `markup.php`, `index.js`, `style.css`, `icon.js` | `<dt>` term block; parent is `tenup/dl-item` | Module 10 |
| `blocks/dd/` | `block.json`, `markup.php`, `index.js`, `style.css`, `icon.js` | `<dd>` description block; parent is `tenup/dl-item` | Module 10 |
| `blocks/movie-runtime/` | `block.json`, `markup.php`, `index.js` | Reads movie runtime from post meta via block context (`postId`, `postType`); no attributes | Module 10 |
| `blocks/rate-movie/` | `block.json`, `markup.php`, `index.js`, `view-module.js`, `style.css` | Interactivity API star rating (1–10); HTML5 popover; range slider; store with state/actions/callbacks; ARIA labeling | Module 11 |

### Summary: delta by count

| Category | Added | Modified | Removed | Total deltas |
|----------|-------|----------|---------|-------------|
| Templates | 4 | 1 | 0 | 5 |
| Template parts | 0 | 2 | 0 | 2 |
| Patterns | 1 | 0 | 0 | 1 |
| Style variations | 2 | 0 | 3 | 5 |
| PHP source | 1 | 3 | 0 | 4 |
| CSS files | 9 | 5 | 0 | 14 |
| JS files | 23 | 1 | 0 | 24 |
| Custom blocks | 6 | 0 | 0 | 6 |
| Config files | 0 | 6 | 0 | 6 |
| **Totals** | **46** | **18** | **3** | **67** |

### Data model (MU plugin — shared across both themes)

The `10up-plugin` MU plugin provides the content model that `fueled-movies` consumes:

- **CPTs**: `tenup-movie` (Movies at `/movies/`), `tenup-person` (People at `/people/`)
- **Taxonomies**: `tenup-genre` (Genre); `tenup-keyword` and `tenup-watch-provider` are referenced in `Movie.php` but have no class definitions
- **Post meta** (15 fields): 9 for Movies (`tenup_movie_imdb_id`, `_mpa_rating`, `_plot`, `_release_year`, `_runtime`, `_viewer_rating`, `_viewer_rating_count`, `_trailer_id`) + 6 for People (`tenup_person_biography`, `_birthplace`, `_born`, `_deathplace`, `_died`, `_imdb_id`)
- **Relationships**: Movie ↔ Person (bidirectional many-to-many via Content Connect)
- **Framework**: `ModuleInterface` + `Module` trait + `ModuleInitialization` auto-discovery

---

## Curriculum (10 lessons)

### Lesson 1 — Orientation, tooling, and the build pipeline

#### Learning Outcomes

1. Understand the 10up scaffold's build pipeline and how `dist/` is produced.
2. Be able to run builds for the theme and MU plugin without errors.
3. Understand the three editor style scopes: **frame**, **canvas**, and **frontend**.

#### Context

The 10up Block Theme scaffold ships with `10up-toolkit` — a zero-config webpack-based build tool. It reads entry points from `package.json`, compiles CSS and JS from `assets/` into `dist/`, and auto-detects blocks in `blocks/`. Both themes use the same pipeline. The MU plugin has its own separate build.

#### Key files

- `themes/10up-block-theme/package.json` — entry points, toolkit config, scripts
- `themes/10up-block-theme/composer.json` — PHP autoloading, framework dependency
- `themes/*/src/Assets.php` — enqueue strategy (`GetAssetInfo` trait, editor + frontend hooks)
- `mu-plugins/10up-plugin/package.json` — plugin build config

#### Tasks

1. **Install and build everything.** Run `composer install` and `npm run build` for the theme and MU plugin. Confirm no fatal errors when loading WP.
2. **Trace an asset from source to browser.** Pick `frontend.css` in `package.json`, find it compiled in `dist/`, then find the PHP that enqueues it in `src/Assets.php`. Do the same for `block-extensions.js`.
3. **Understand the three style scopes.** Open the editor and use DevTools to identify:
   - **Frame styles**: the editor chrome outside the canvas iframe (enqueued via `enqueue_block_editor_assets`)
   - **Canvas styles**: the iframe where blocks render (loaded via `add_editor_style()`)
   - **Frontend styles**: the actual site (enqueued via `wp_enqueue_scripts`)

> 📐 **Diagram suggestion**: A labeled diagram of the editor showing the frame (toolbar, sidebar) and the canvas iframe, with arrows pointing to where each stylesheet scope applies. This is the single most confusing concept for newcomers.

4. **Add a new CSS entry.** Create a small CSS file, add it as an entry in `package.json`, rebuild, and verify it loads where you expect.

#### Takeaways

- `10up-toolkit` builds assets. `dist/` is what WordPress loads — never edit it directly.
- `src/Assets.php` is where all enqueuing happens.
- Frame, canvas, and frontend are three separate CSS scopes. Getting them confused is the #1 gotcha.

#### Ship it checkpoint

- Builds succeed for theme + MU plugin
- You can point to where each of the three style scopes is enqueued in PHP

#### Further reading

- `gutenberg-best-practices/training/Block-Based-Themes/index.md`
- `gutenberg-best-practices/training/Block-Based-Themes/02-10up-block-theme.md`
- `gutenberg-best-practices/reference/02-Themes/styles.md`

---

### Lesson 2 — Templates, template parts, and the Site Editor

#### Learning Outcomes

1. Understand the relationship between HTML template files and the Site Editor.
2. Know how to create, edit, and export templates and parts.
3. Understand why block markup must be valid (no arbitrary HTML in templates).
4. Know when to use a template vs a template part vs a pattern.

#### Context

Block themes replace PHP templates with HTML files containing block markup. The template hierarchy still applies — `templates/index.html` maps to `index.php`, and so on. The `10up-block-theme` ships four core templates and four parts. The `fueled-movies` theme adds four CPT-specific templates and modifies `index.html` to use inline card markup instead of a pattern reference.

#### Key files

- `themes/10up-block-theme/templates/*.html` and `parts/*.html`
- `themes/fueled-movies/templates/archive-tenup-movie.html` (target state)
- `themes/fueled-movies/theme.json` → `customTemplates` array

#### Tasks

1. **Inspect an existing template.** Open `templates/index.html` in the code editor and the Site Editor side by side. Identify each `<!-- wp: -->` comment and the block it represents.

> 📷 **Screenshot suggestion**: Side-by-side of the code editor view (block markup) and the visual Site Editor rendering of the same template. This makes the connection between markup and visuals click immediately.

2. **Create a new template part.** In the Site Editor, build a new template part (e.g. a banner). Export it back to the theme using the [Create Block Theme](https://wordpress.org/plugins/create-block-theme/) plugin.

> 📷 **Screenshot suggestion**: The Create Block Theme export UI, showing the "Save Changes to Theme" option.

3. **Wire it into a template.** Add a `<!-- wp:template-part {"slug":"your-part"} /-->` reference into a template. Verify it renders on the frontend.
4. **Register a custom template.** Add a `customTemplates` entry to `theme.json` so the editor knows about a CPT-specific template. This is how `fueled-movies` makes `archive-tenup-movie` discoverable in the template picker.

:::tip
No one should have to hand-author block markup in `.html` files. The Site Editor is the best tool for composing templates. Use the Create Block Theme plugin to export your work back to theme files.
:::

#### Takeaways

- Templates are HTML files with block markup. The template hierarchy still applies.
- Template parts are reusable chunks (header, footer, sidebars) referenced from templates.
- Author templates in the Site Editor and export — don't hand-write block markup.
- `customTemplates` in `theme.json` makes templates visible for specific post types.

#### Ship it checkpoint

- A real change exists in `templates/` or `parts/` that renders on the frontend
- The change was authored in the Site Editor and exported back to theme files
- Templates remain valid block markup

#### Further reading

- `gutenberg-best-practices/training/Block-Based-Themes/01-overview.md`
- `gutenberg-best-practices/reference/02-Themes/block-based-templates.md`
- `gutenberg-best-practices/reference/02-Themes/block-template-parts.md`

---

### Lesson 3 — theme.json: design tokens and settings

#### Learning Outcomes

1. Know what belongs in `theme.json` (tokens, settings, layout constraints) and what doesn't (actual CSS).
2. Understand the cascade: default → theme → user.
3. Be able to add a new color, spacing size, or typography preset and use it in a template.
4. Know how theme.json values become CSS custom properties.

#### Context

There's a misconception that block themes put all their styles in `theme.json`. This is not the case, and fighting core stylesheet specificity is a losing battle.

The rule of thumb: **`theme.json` is the source of truth for design tokens and settings. Actual styles belong in CSS files.**

Compare the two theme.json files: `10up-block-theme` ships minimal settings (spacing, layout). `fueled-movies` adds an 8-color palette, semantic custom properties, a custom aspect ratio (Movie Poster 2:3), element-level button/link styles, and `customTemplates`.

#### Key files

- `themes/10up-block-theme/theme.json` (baseline)
- `themes/fueled-movies/theme.json` (target state)

#### Tasks

1. **Compare both theme.json files.** Note what `fueled-movies` adds: palette colors, `settings.custom` properties, element styles, and `customTemplates`.
2. **Add a new preset color.** Add it to `settings.color.palette`, rebuild, and verify it appears in the editor's color picker.

> 📷 **Screenshot suggestion**: The color picker in the block editor showing the custom palette, with the newly added color visible.

3. **Use the token.** Apply the new color to a block. Inspect the HTML and find the generated CSS custom property (`--wp--preset--color--your-slug`).
4. **Add a custom property.** Under `settings.custom`, add a value (e.g. a border radius token). Use it in a CSS file via `var(--wp--custom--your-slug)`.

:::caution
Avoid putting layout or visual styles in `theme.json` `styles` beyond element-level defaults. For anything more specific, use CSS. Core stylesheet specificity will fight you otherwise.
:::

#### Takeaways

- `theme.json` is for tokens and settings. CSS is for styles.
- Every preset generates a CSS custom property you can use anywhere.
- `settings.custom` creates `--wp--custom--*` variables for anything you need.
- The cascade is default → theme → user. User overrides in the Site Editor win.

#### Ship it checkpoint

- A new token is added to `theme.json` and used somewhere
- Removing the token causes an obvious regression

#### Further reading

- `gutenberg-best-practices/reference/02-Themes/theme-json.md`
- `gutenberg-best-practices/reference/02-Themes/styles.md`

---

### Lesson 4 — Styles: CSS architecture and style variations

This lesson combines two related topics: how the scaffold organizes and code-splits CSS, and how style variations give editors controlled choices.

#### Learning Outcomes

1. Understand the autoenqueue pipeline: `assets/css/blocks/{namespace}/{block-name}.css` → loaded only when the block is present.
2. Know the difference between block CSS, component CSS, and base CSS.
3. Know what style variations are (`styles/{block-name}/{slug}.json`) and how they differ from JS-registered block styles.
4. Be able to create a style variation and a code-split block stylesheet.

#### Context

WordPress already code-splits its own block styles — it only loads image CSS when an image block is present. The 10up scaffold piggybacks on this. Files in `assets/css/blocks/core/` are built to `dist/blocks/autoenqueue/core/` and registered via `src/Blocks.php`. The theme only loads them when needed.

Meanwhile, the `10up-block-theme` ships three global "surface" style variations. The `fueled-movies` theme replaces these with targeted per-block variations: `styles/button/secondary.json` and `styles/group/secondary.json`. These show up as selectable options in the editor.

#### Key files

- `themes/fueled-movies/assets/css/blocks/core/` — per-block CSS source files (5 new in fueled-movies)
- `themes/fueled-movies/assets/css/components/` — component CSS (header, card, button — always loaded)
- `themes/fueled-movies/src/Blocks.php` — `auto_enqueue_block_styles()` method
- `themes/fueled-movies/styles/group/secondary.json` — Group style variation
- `themes/fueled-movies/styles/button/secondary.json` — Button style variation

#### Tasks

**Part A: Code-split CSS**

1. **Trace the autoenqueue pipeline.** Open `src/Blocks.php` and find the method that globs `dist/blocks/autoenqueue/` and calls `wp_enqueue_block_style()`.
2. **Add a new core block stylesheet.** Create `assets/css/blocks/core/separator.css` with visible styling. Rebuild.
3. **Verify code-splitting.** Load a page with a Separator block — your styles should load. Load a page without one — they shouldn't. Use DevTools Network tab to confirm.

> 📷 **Screenshot suggestion**: DevTools Network panel showing the block-specific stylesheet loading on one page but not another. This is the "aha" moment for code-splitting.

4. **Compare with component CSS.** Look at `assets/css/components/button.css` — it loads globally via `frontend.css`. Understand when to use block-scoped CSS vs component CSS vs base CSS.

**Part B: Style variations**

5. **Read the existing variations.** Open `styles/group/secondary.json` and `styles/button/secondary.json`. Note the structure: `title`, `slug`, `blockTypes`, and nested `styles` object.
6. **Create a Group variation.** Add `styles/group/highlight.json` with a distinct background and border. Rebuild and verify it appears in the editor.

> 📷 **Screenshot suggestion**: The Styles panel in the block inspector showing the new "Highlight" variation alongside "Default" and "Secondary".

7. **Create a Button variation.** Add `styles/button/outline.json`. Target the inner `.wp-block-button__link`, not the outer wrapper.
8. **Test in both scopes.** Verify your variations work in the editor canvas and on the frontend.

:::caution
Button style variations are a known pain point. The editor and frontend markup differ slightly, so `elements.link` vs `elements.button` targeting may not behave the same in both contexts. Always test both.
:::

#### Takeaways

- Block-scoped CSS loads per-block. Component CSS loads globally. Choose intentionally.
- WordPress inlines small stylesheets as critical CSS — block-scoped CSS benefits from this automatically.
- Style variations are JSON files that give editors controlled styling choices.
- Button variations need to target `.wp-block-button__link`, not the wrapper.
- A block should never have more than 4 style variations. Keep them intentional.

#### Ship it checkpoint

- A core block stylesheet is code-split and only loads when the block is present
- A Group and Button style variation are selectable in the editor and work on the frontend

#### Further reading

- `gutenberg-best-practices/training/Block-Based-Themes/01-overview.md` (section on writing CSS for individual blocks)
- `gutenberg-best-practices/reference/02-Themes/styles.md`
- `gutenberg-best-practices/reference/03-Blocks/block-styles.md`
- `gutenberg-best-practices/guides/pitfals-style-api.md`

---

### Lesson 5 — Patterns as a composition tool

#### Learning Outcomes

1. Know the difference between a pattern used for starter content and one used to compose templates.
2. Understand pattern metadata (Title, Slug, Categories, Post Types, Inserter visibility).
3. Be able to create a pattern and use it inside a template.

#### Context

The `10up-block-theme` ships one pattern (`patterns/card.php`) — starter content that gets copied on insert with no ongoing link to the original.

The `fueled-movies` theme takes patterns further. `patterns/single-movie-trailer.php` exists specifically to be referenced from a template, keeping the template readable while encapsulating a reusable chunk. This is the shift from "patterns as convenience" to **patterns as architecture**.

#### Key files

- `themes/10up-block-theme/patterns/card.php` — starter content pattern
- `themes/fueled-movies/patterns/single-movie-trailer.php` — composition pattern
- `themes/fueled-movies/templates/single-tenup-movie.html` — template that references the pattern

#### Tasks

1. **Read both patterns.** Compare `card.php` (generic starter content) with `single-movie-trailer.php` (template composition). Note the PHP header metadata in each.
2. **Create a composition pattern.** Build a "movie details sidebar" pattern. Set `Inserter: false` if it's only for template use.

> 📷 **Screenshot suggestion**: The pattern inserter panel in the Site Editor, showing how patterns appear (and how `Inserter: false` hides them from this view while still allowing template references).

3. **Use it in a template.** Reference your pattern with `<!-- wp:pattern {"slug":"your-slug"} /-->`. Verify it renders.
4. **Restrict to a post type.** Add `Post Types: tenup-movie` so it only appears when editing Movies.

#### Takeaways

- Patterns serve two roles: starter content (copied and detached) or template composition (referenced).
- `Inserter: false` hides a pattern from the inserter while keeping it usable in templates.
- `Post Types` restricts a pattern to specific post types.
- Regular patterns have no ongoing link to instances — changes to the file don't update already-placed content.

#### Ship it checkpoint

- A new pattern exists in `patterns/` with correct PHP header metadata
- The pattern is referenced from a template and renders on the frontend

#### Further reading

- `gutenberg-best-practices/reference/04-Patterns/overview.md`
- `gutenberg-best-practices/training/Blocks/04-patterns.md`
- `gutenberg-best-practices/reference/04-Patterns/synced-patterns.md`

---

### Lesson 6 — Content model: CPTs, taxonomies, and post meta

#### Learning Outcomes

1. Know how CPTs, taxonomies, and post meta are defined in the MU plugin.
2. Understand the `ModuleInterface` pattern and how modules auto-register.
3. Be able to add a new meta field and confirm it appears in the REST API.
4. Understand why `show_in_rest` is the key that unlocks everything downstream.

#### Context

The content model lives in the MU plugin, not the theme — data should persist regardless of which theme is active. The `10up-plugin` uses the 10up PHP framework (`ModuleInterface` + `Module` trait + `ModuleInitialization`) to auto-discover and register classes in `src/`.

The plugin defines two CPTs (Movie, Person), one taxonomy (Genre), 15 post meta fields, and one bidirectional relationship (Movie ↔ Person via Content Connect).

#### Key files

- `mu-plugins/10up-plugin/src/PluginCore.php` — bootstrap and module initialization
- `mu-plugins/10up-plugin/src/PostTypes/Movie.php` — Movie CPT definition
- `mu-plugins/10up-plugin/src/Taxonomies/Genre.php` — Genre taxonomy
- `mu-plugins/10up-plugin/src/PostMeta/AbstractPostMeta.php` — base class for meta fields
- `mu-plugins/10up-plugin/src/PostMeta/MovieRuntime.php` — complex object meta example
- `mu-plugins/10up-plugin/src/Relationships.php` — Content Connect relationship

#### Tasks

1. **Trace the initialization flow.** Start at `plugin.php`, follow to `PluginCore.php`, and see how `ModuleInitialization::instance()->init_classes()` auto-discovers classes.

> 📐 **Diagram suggestion**: A flowchart showing the module discovery path: `plugin.php` → `PluginCore::init()` → `ModuleInitialization` → scans `src/` → calls `can_register()` → calls `register()` for each class. This makes the "magic" of auto-discovery concrete.

2. **Read a CPT definition.** Open `PostTypes/Movie.php`. Note the slug, rewrite, supported taxonomies, and `custom-fields` support.
3. **Read the abstract meta class.** Open `AbstractPostMeta.php`. See how `register_post_meta()` is called with `show_in_rest`, `single`, `type`, and optional `default`/`enum`.
4. **Add a new meta field.** Create a class (e.g. `MovieTagline.php`) extending `AbstractPostMeta`. Set the key, type, and default. Verify it appears in the REST response: `/wp-json/wp/v2/tenup-movie/{id}`.
5. **Test the relationship.** Relate a Movie to a Person via the Content Connect UI. Verify it persists.

#### Takeaways

- The content model belongs in the MU plugin. Data outlives design.
- All modules implement `ModuleInterface` with `can_register()`, `register()`, and `load_order()`.
- `show_in_rest` is the single most important flag — without it, the field is invisible to the editor, bindings, and JS.
- Complex meta (like `MovieRuntime`) uses the `object` type with a `properties` schema.

#### Ship it checkpoint

- The CPT works end-to-end (archive + single resolve, permalinks work)
- A meta field is registered with `show_in_rest` and persists when edited via REST

#### Further reading

- `gutenberg-best-practices/reference/05-custom-post-types.md`

---

### Lesson 7 — Editor controls for post meta

#### Learning Outcomes

1. Know how to build editor UI for meta fields using `PluginDocumentSettingPanel`.
2. Be able to add a control that reads and writes post meta via `useEntityProp`.
3. Understand how to scope a sidebar panel to a specific post type.

#### Context

WordPress no longer needs custom metaboxes. The block editor's SlotFill system lets us inject panels into the document sidebar, scoped to specific post types.

The `fueled-movies` theme registers two panels — one for Movie fields, one for Person fields — each rendering reusable components from `assets/js/block-components/PostMeta/`.

#### Key files

- `themes/fueled-movies/assets/js/block-plugins/movie-meta-fields.js` — Movie sidebar panel
- `themes/fueled-movies/assets/js/block-plugins/person-meta-fields.js` — Person sidebar panel
- `themes/fueled-movies/assets/js/block-components/PostMeta/` — 16 reusable meta field components

#### Tasks

1. **Read the movie meta panel.** Open `movie-meta-fields.js`. Identify the `PluginDocumentSettingPanel` wrapper and how it checks the current post type.

> 📷 **Screenshot suggestion**: The document sidebar in the editor for a Movie post, showing the custom "Movie Details" panel with fields for IMDB ID, MPA Rating, Plot, Runtime, etc. This is the end state we're building toward.

2. **Read a meta component.** Open a PostMeta component (e.g. `MovieRuntime`). Note how `useEntityProp` reads and writes meta values.
3. **Add a new control.** If you added `MovieTagline` in Lesson 6, create a corresponding editor component with `TextControl`. Import it into `movie-meta-fields.js`.

> 📷 **Screenshot suggestion**: Before/after of the sidebar panel — without the tagline field, then with it added.

4. **Test persistence.** Set a value, save, refresh. Confirm it persists. Check the REST API.

:::tip
The pattern for reading/writing post meta in the editor:

```js
const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
const value = meta?.your_meta_key ?? '';
const onChange = (newValue) => setMeta({ ...meta, your_meta_key: newValue });
```
:::

#### Takeaways

- Use `PluginDocumentSettingPanel`, not custom metaboxes.
- `useEntityProp` is the standard hook for reading/writing post meta in the editor.
- Scope panels to the correct post type.
- Keep meta components small — one component per field.

#### Ship it checkpoint

- A document panel appears only for the intended post type
- Editing a field updates post meta and persists after refresh

#### Further reading

- `gutenberg-best-practices/training/Blocks/08-slot-fill.md`
- `gutenberg-best-practices/guides/tools-panel.md`

---

### Lesson 8 — Block Bindings API

#### Learning Outcomes

1. Understand the Block Bindings API: what it does, which blocks support it, and its limitations.
2. Know how to register a custom binding source in PHP with a callback.
3. Know how to register an editor-side preview so bindings show placeholder text while editing.
4. Understand null/empty fallback strategy.

#### Context

The Block Bindings API (WordPress 6.6+) lets core blocks read dynamic values from a source — post meta, computed data, relationship queries. You don't need a custom block for every piece of dynamic text.

Currently only Image, Paragraph, Heading, and Button support bindings.

The `fueled-movies` theme implements this end-to-end:
- **PHP** (`src/BlockBindings.php`): registers `tenup/block-bindings` and routes 8 binding keys to helper methods.
- **JS** (`assets/js/block-bindings/index.js`): registers an editor-side source that provides placeholder text.
- **Templates**: `single-tenup-movie.html` and `single-tenup-person.html` use `metadata.bindings` throughout.

#### Key files

- `themes/fueled-movies/src/BlockBindings.php` — PHP source registration + callbacks
- `themes/fueled-movies/assets/js/block-bindings/index.js` — editor preview registration
- `themes/fueled-movies/templates/single-tenup-movie.html` — binding usage

#### Tasks

1. **Read the PHP source.** Open `BlockBindings.php`. Find `register_block_bindings_source()`. Trace one key (e.g. `movieStars`) through the callback to the Content Connect query.
2. **Read the editor preview.** Open `block-bindings/index.js`. See how it returns placeholder strings for the same keys.

> 📷 **Screenshot suggestion**: The Site Editor showing a single movie template, with bound Paragraph blocks displaying placeholder text like "Stars: Actor Name, Actor Name". Then the same template on the frontend showing real data. Side by side makes the two-source concept clear.

3. **Add a new binding key.** Add a key for `movieTagline`:
   - PHP: add a case to the callback.
   - JS: add a placeholder value.
4. **Use it in a template.** Add a Paragraph block with binding attributes in the Code Editor view.
5. **Test both scopes.** Editor shows placeholder. Frontend shows real data. Empty meta returns `null`.

:::caution
Bound blocks always render their markup, even when the value is empty. An empty `<p></p>` will appear on the frontend. Return `null` from your PHP callback when there's nothing to show.
:::

#### Takeaways

- Block bindings let core blocks display dynamic values without custom blocks.
- You need both a PHP source (real values) and a JS source (editor previews).
- Only Image, Paragraph, Heading, and Button support bindings today.
- Always handle null/empty values. Return `null` when there's nothing to show.
- Bindings are not conditional — you can't hide a bound block entirely.

#### Ship it checkpoint

- A new binding key exists in both PHP and JS
- The binding works: editor shows placeholder, frontend shows real value
- Empty inputs don't break rendering

#### Further reading

- `gutenberg-best-practices/reference/04-Patterns/block-bindings-api.md`

---

### Lesson 9 — Building and extending blocks

This lesson covers both building custom blocks from scratch and extending existing core blocks with new behavior. These are two sides of the same coin: when do you build new, and when do you extend what's already there?

#### Learning Outcomes

1. Understand a custom block's anatomy: `block.json`, `edit.js`, `index.js`, `markup.php`, `style.css`.
2. Know how to build parent/child block relationships using `parent` and `allowedBlocks`.
3. Be able to create a dynamic block that renders via PHP.
4. Know how to extend a core block with custom attributes and controls using block filters.
5. Know when to build a custom block vs extend a core one.

#### Context

The `fueled-movies` theme demonstrates both approaches:

**Custom blocks**: A four-block description list system (`tenup/dl`, `tenup/dl-item`, `tenup/dt`, `tenup/dd`) plus `tenup/movie-runtime`. These are dynamic blocks — server-rendered via PHP — which avoids block deprecation headaches.

**Block extensions**: `assets/js/block-filters/group.js` extends the core Group block with a `has-separator` toggle and class name, paired with corresponding CSS in `assets/css/blocks/core/group.css`.

#### Key files

- `themes/fueled-movies/blocks/dl/` — parent DL block (with `allowedBlocks`)
- `themes/fueled-movies/blocks/dl-item/` — item wrapper (with `parent`)
- `themes/fueled-movies/blocks/dt/` and `blocks/dd/` — leaf blocks
- `themes/fueled-movies/blocks/movie-runtime/` — meta display block (uses block context)
- `themes/fueled-movies/assets/js/block-filters/group.js` — core block extension
- `themes/fueled-movies/src/Blocks.php` — auto-registration and render filters

#### Tasks

**Part A: Custom blocks**

1. **Read the DL block family.** Start with `blocks/dl/block.json`. Note `allowedBlocks` (only `tenup/dl-item`). Then `dl-item/block.json` — note `parent`. Follow the chain through `dt` and `dd`.

> 📐 **Diagram suggestion**: A tree diagram showing the block nesting hierarchy: `tenup/dl` → `tenup/dl-item` → `tenup/dt` + `tenup/dd`, with the `parent` and `allowedBlocks` constraints labeled on each connection.

2. **Read the PHP markup.** Open `blocks/dl/markup.php`. See how `get_block_wrapper_attributes()` generates the wrapper and `$content` renders inner blocks.
3. **Read the editor component.** Open `blocks/dl/index.js`. See how `useBlockProps` and `useInnerBlocksProps` wire up the editor.

> 📷 **Screenshot suggestion**: The block inserter showing only `tenup/dl-item` as an option when inside a DL block, demonstrating how `allowedBlocks` constrains the UI.

4. **Create a new block.** Use `npm run scaffold:block` to generate one. Implement it as a dynamic block with `markup.php`. Use it in a template.

**Part B: Block extensions**

5. **Read the Group filter.** Open `block-filters/group.js`. Identify the three-filter pattern:
   - `blocks.registerBlockType` — adds a new attribute
   - `editor.BlockEdit` — renders an inspector control
   - `blocks.getSaveContent.extraProps` — adds a class name

> 📷 **Screenshot suggestion**: The Group block's inspector panel showing the custom "Separator" toggle that the extension adds. Before and after the toggle, showing the visual change.

6. **Add a new extension.** Pick a core block and add a boolean toggle. Follow the same three-filter pattern.

:::tip
When deciding between a custom block and an extension: if you need entirely new markup and behavior, build a block. If you need to add a feature to something that already exists, extend it.
:::

#### Takeaways

- Custom blocks: `block.json` for metadata, `edit.js` for the editor, `markup.php` for the frontend.
- Dynamic blocks (PHP-rendered) avoid deprecation problems — the 10up standard.
- Use `parent` and `allowedBlocks` to enforce nesting rules.
- Block extensions use a three-filter pattern: add attribute → add editor control → add class output.
- Build custom when core blocks can't do the job. Extend when they almost can.

#### Ship it checkpoint

- A custom block exists with `block.json` + editor + PHP render, used in a template
- A core block gains a new toggle/control that adds a class and visible styling

#### Further reading

- `gutenberg-best-practices/reference/03-Blocks/custom-blocks.md`
- `gutenberg-best-practices/reference/03-Blocks/inner-blocks.md`
- `gutenberg-best-practices/reference/03-Blocks/block-extensions.md`
- `gutenberg-best-practices/guides/extend-a-core-block.md`

---

### Lesson 10 — Interactivity API: rate a movie

#### Learning Outcomes

1. Understand the Interactivity API: stores, state, actions, callbacks, and `data-wp-*` directives.
2. Know how to wire a `view-module.js` to a block via `block.json`.
3. Be able to build interactive UI with proper accessibility.
4. Understand progressive enhancement — the block should be meaningful without JS.

#### Context

The `tenup/rate-movie` block is a fully interactive star rating widget. It uses an HTML5 popover dialog, a range slider, a store with state/actions/callbacks, and proper ARIA attributes.

The Interactivity API replaces ad-hoc frontend JavaScript with a declarative system. Instead of `querySelector` and event listeners, you use `data-wp-on--click`, `data-wp-bind--aria-expanded`, `data-wp-text`, etc.

#### Key files

- `themes/fueled-movies/blocks/rate-movie/block.json` — metadata with `viewScriptModule`
- `themes/fueled-movies/blocks/rate-movie/markup.php` — server-rendered HTML with directives
- `themes/fueled-movies/blocks/rate-movie/view-module.js` — store definition
- `themes/fueled-movies/blocks/rate-movie/style.css` — block styles

#### Tasks

1. **Read the markup.** Open `markup.php`. Identify each `data-wp-*` directive and map it to a state value or action in the store.

> 📐 **Diagram suggestion**: An annotated version of the rate-movie markup, with arrows from each `data-wp-*` directive to the corresponding state/action in `view-module.js`. This is a lot of wiring to hold in your head — a visual map helps.

2. **Read the store.** Open `view-module.js`. Find state declarations, actions (`selectRating`, `clearRating`), and callbacks (`initPopover`).
3. **Trace the interaction flow.** Follow what happens when a user clicks "Rate this movie": popover opens → slider sets value → button text updates → state reflects rating.

> 📷 **Screenshot suggestion**: The rate-movie popover open on the frontend, with the range slider visible and a rating selected. Then the closed state showing the rating text on the button.

4. **Add a new interaction.** Extend the block — e.g. display a text label ("Poor", "Average", "Great") based on the rating using `data-wp-text` bound to a computed state value.
5. **Test accessibility.** Use VoiceOver (macOS) or NVDA to navigate the popover. Verify `aria-expanded` toggles, the popover is labeled, and keyboard navigation works.

:::caution
The Interactivity API is not a replacement for React. It's designed for server-rendered blocks that need client-side behavior. Editor-side interactivity still lives in `edit.js` with React.
:::

#### Takeaways

- The Interactivity API adds frontend behavior to server-rendered blocks declaratively.
- Directives (`data-wp-on--click`, `data-wp-bind--*`, `data-wp-text`) connect HTML to store state.
- Always server-render the initial HTML in `markup.php`. The API enhances it, not replaces it.
- Accessibility is not optional: ARIA attributes, keyboard support, screen reader testing.
- State rules should be enforced: null initial values, range clamping, clear/reset behavior.

#### Ship it checkpoint

- The block uses a view module with store state/actions (no console errors)
- Accessibility: popover labeling, `aria-expanded`, keyboard navigation all work
- State rules enforced (null initial, range 1–10, clear resets to null)

#### Further reading

- `gutenberg-best-practices/guides/interactivity-api-getting-started.md`

---

## Exercise template

Use this format for standalone exercises (keep them small and testable):

- **Goal** — one sentence
- **Context** — what exists already, what you're changing
- **Files to edit** — explicit paths
- **Tasks** — numbered steps
- **Done when** — observable behavior
- **Extra credit** — stretch goal
- **Further reading** — links into `gutenberg-best-practices/**`

---

## Ship-it review checklist (use on every module)

- **Scope**: smallest possible diff; changes live in the correct layer (theme vs MU plugin vs template vs block)
- **User-visible**: demonstrate in the **Site Editor** and on the **frontend**
- **Persistence**: refresh confirms data/settings persist (meta, templates, etc.)
- **Naming**: consistent slugs/keys (block names, meta keys, taxonomy slugs, all prefixed `tenup-` or `tenup_`)
- **Safety**: no PHP notices/warnings; no block rendering errors; safe fallbacks where data may be missing
- **Accessibility**: new UI includes keyboard support and appropriate ARIA attributes
- **Performance**: new assets are code-split where possible; no unnecessary global stylesheet bloat

---

## Suggested new lessons for gutenberg-best-practices

The following topics are demonstrated in the `fueled-movies` theme but are not yet covered (or only partially covered) in the `gutenberg-best-practices` docs. Each suggestion includes a recommended format and where it would fit in the existing structure.

### 1. Editor style scopes: frame vs canvas vs frontend

**Where**: `reference/02-Themes/` or `training/Block-Based-Themes/`

Block themes have three distinct CSS scopes that behave very differently. This trips up almost everyone the first time. The lesson should cover:

- What the editor "frame" is (the chrome outside the editing canvas) and when you'd style it
- What the editor "canvas" is (the iframe where blocks render) and how `add_editor_style()` loads CSS there
- How frontend styles load normally via `wp_enqueue_scripts`
- The three corresponding entry points in `package.json` and `src/Assets.php`
- Common mistakes: loading canvas styles in the frame scope (they don't apply) or loading editor-only styles on the frontend

**Tone**: Conceptual explanation with diagrams, then a practical "trace the CSS" exercise.

### 2. The autoenqueue pipeline: code-split CSS for core blocks

**Where**: `training/Block-Based-Themes/` (new lesson) or `reference/02-Themes/styles.md` (expanded section)

The 10up scaffold's autoenqueue approach is a key performance feature, but it's not documented anywhere. The lesson should cover:

- The `assets/css/blocks/{namespace}/{block-name}.css` naming convention
- How `src/Blocks.php` globs `dist/blocks/autoenqueue/` and calls `wp_enqueue_block_style()`
- How WordPress inlines small stylesheets as critical CSS
- When to use block-scoped CSS vs component CSS vs base CSS
- A hands-on exercise: add a stylesheet for `core/separator`, rebuild, verify it only loads when the block is present

**Tone**: Practical walkthrough with "verify in DevTools" checkpoints.

### 3. Style variations as a system (targeted per-block JSON)

**Where**: `reference/02-Themes/` (new page) or `reference/03-Blocks/block-styles.md` (expanded section)

The existing block styles docs focus on JS-registered styles. Style variations via JSON files in `styles/` are a different mechanism with different tradeoffs. The lesson should cover:

- The `styles/{block-name}/{slug}.json` file structure
- The difference between JS-registered block styles and JSON style variations
- How to target nested elements (e.g. `.wp-block-button__link` inside a Button variation)
- The Button gotcha: editor vs frontend markup differences
- When to use style variations vs block styles vs block extensions
- A decision tree for choosing the right approach

**Tone**: Reference format with a comparison table and a "choose your adventure" flowchart.

### 4. Block Bindings with editor previews (the JS side)

**Where**: `reference/04-Patterns/block-bindings-api.md` (expanded) or new guide in `guides/`

The existing block bindings docs only cover the PHP side. The JS editor preview is equally important — without it, editors see empty blocks in the canvas. The lesson should cover:

- Registering a client-side binding source with `registerBlockBindingsSource`
- The `getValues` callback and how it maps keys to placeholder strings
- How the editor resolves bindings: JS source for preview, PHP source for frontend
- Best practice: keep placeholder text realistic so editors understand what will render
- Gotcha: the JS source must use the same slug as the PHP source

**Tone**: Build on the existing block bindings reference doc with a new "Editor Preview" section.

### 5. Document sidebar panels for post meta (SlotFill in practice)

**Where**: `training/Block-Based-Themes/` (new lesson) or `guides/` (new guide)

The existing SlotFill lesson (Blocks training #8) covers the concept but doesn't show the most common real-world use case: document-level sidebar panels for CPT meta fields. The lesson should cover:

- `PluginDocumentSettingPanel` component usage
- `useEntityProp` for reading/writing post meta in the editor
- Scoping panels to specific post types (check `currentPostType` before rendering)
- Building reusable meta field components (one component per field)
- The relationship between `show_in_rest` in PHP and `useEntityProp` in JS
- A hands-on exercise: add a new meta field (PHP), create an editor component (JS), wire it into a sidebar panel

**Tone**: Training lesson format with Learning Outcomes, step-by-step Tasks, and a complete working example.

### 6. Parent/child block architecture (semantic composition)

**Where**: `reference/03-Blocks/inner-blocks.md` (expanded) or `training/Block-Based-Themes/` (new lesson)

The existing InnerBlocks docs cover the API but don't show a real-world multi-level nesting pattern. The DL/DT/DD block family in `fueled-movies` is a perfect case study. The lesson should cover:

- Designing a block hierarchy (DL → DL-Item → DT + DD)
- Using `parent` and `allowedBlocks` to enforce structure
- Template locking strategies (`templateLock` options and when to use each)
- Why each level needs its own `block.json`, editor component, and PHP render
- How inner block content flows through `$content` in `markup.php`
- Accessibility: semantic HTML output from composable blocks

**Tone**: Case study format — "we built this, here's why" — with code examples from the actual blocks.

### 7. Content model to binding pipeline (end-to-end guide)

**Where**: `guides/` (new guide)

No single document currently traces the full path from defining a meta field in the MU plugin to rendering it dynamically in a theme template via block bindings. The lesson should cover the complete pipeline:

1. Define meta field in PHP (`register_post_meta` with `show_in_rest`)
2. Create editor component for the field (`useEntityProp`)
3. Register binding source in PHP (`register_block_bindings_source` with callback)
4. Register editor preview in JS (`registerBlockBindingsSource` with `getValues`)
5. Use the binding in a template (`metadata.bindings` in block markup)
6. Handle null/empty values gracefully

**Tone**: End-to-end walkthrough, from "you have a new field to display" to "it renders on the frontend."

### 8. Interactivity API accessibility patterns

**Where**: `guides/interactivity-api-getting-started.md` (expanded section) or new guide

The existing Interactivity API guide covers the basics but doesn't address accessibility. The `rate-movie` block is a good example of doing it right. The lesson should cover:

- Server-rendering accessible initial state in `markup.php`
- Using `data-wp-bind--aria-expanded`, `data-wp-bind--aria-label`, etc. for live ARIA updates
- HTML5 popover pattern with proper labeling (`aria-modal`, `aria-labelledby`)
- Keyboard navigation: ensuring focus management works without custom JS
- Testing strategy: screen reader walkthrough + keyboard-only navigation

**Tone**: Guide format with "do this / don't do this" examples and a testing checklist.

### 9. Extending core blocks with filters (practical guide)

**Where**: `guides/extend-a-core-block.md` (expanded) or `training/Block-Based-Themes/` (new lesson)

The existing guide on extending core blocks shows `registerBlockExtension` from `@10up/block-components`. A complementary lesson on the raw WordPress filter approach (`blocks.registerBlockType`, `editor.BlockEdit`, `blocks.getSaveContent.extraProps`) would round out the topic. The `fueled-movies` Group filter is a compact, real-world example.

**Tone**: Practical walkthrough showing the three-filter pattern with a complete working example.

### 10. View transitions in block themes

**Where**: `guides/` (new guide) or `reference/02-Themes/` (new page)

The `fueled-movies` theme uses `view-transition-name` on featured images (set via a render filter in `src/Blocks.php`). This is an emerging browser feature that block themes are well-positioned to leverage. The lesson should cover:

- What view transitions are and current browser support
- Adding `view-transition-name` via the `render_block` filter
- Scoping transitions to specific blocks (featured image, post title)
- CSS for transition animations
- Progressive enhancement: transitions are optional and don't break non-supporting browsers

**Tone**: Short guide format — emerging feature, not a core curriculum item.

---

## Open questions / TODOs

- Block styles (Button): why `core/button` style variation JSON does not apply as expected in the editor in certain cases (targeting `elements.link` vs `elements.button`, editor markup differences, registration expectations). This is a known pain point worth investigating and documenting.
- Maybe consider a bonus lesson explaining how to mirror core components when you need, such as the themes datetime picker modeled after the core post publish date picker.
- Discuss orphaned label problem in our theme, notably Persons who haven't died.  Discuss fallbacks and that we are opting to return empty strings but maybe you want n/a or something.
- Ensure we discuss do_blocks approach for outputting block classed markup and code, see rating block.
- Discuss that we have purely decorative elements such as a button with no link for viewerRatingLabelText or empty "Star + Rate" in cards.  Consider updating to remove these perhaps or maybe combining interactive api rate block example with that button for just one UI piece that maybe outputs initial IMDB but would change text if user interacts.  Another option might be showing how to add interactivity to a block via render_block instead of markup.php file and then just have it do something stupid like alert("you clicked me").
- Movie cards do not match FE in editor because wp_template is post type.  Consider just call it out in training, separate card patterns, or have user create "dumb" custom block that can check for meta before rendering pieces, maybe good training for post context.
- Can note Avengers as movie with no trailer showing fallback.
- What to do for the homepage of the theme? Seems a bit redundant.
- Maybe mention we can keep images in patterns dir.
