# Fueled Movies Import CLI — Requirements

## Overview

A single WP-CLI command that populates a fresh WordPress install with movie and person content from the IMDB API. Designed for the block-based theme training project — users clone the repo, create a local site, and run one command to get a working dataset.

```bash
wp fueled-movies import
```

The command lives in `mu-plugins/10up-plugin/src/CLI/`.

## Usage

```bash
# Import the 30 default movies + their star cast
wp fueled-movies import

# Import only specific movies + their cast
wp fueled-movies import --ids=tt0910970,tt0068646

# Preview without creating posts
wp fueled-movies import --dry-run

# Override default star limit (default: 3 per movie)
wp fueled-movies import --star-limit=5
```

### Behavior

- **No arguments**: prompts the user to confirm importing the 30 default movies with their cast.
- **`--ids`**: imports **only** the specified IMDB IDs. The default list is not used.
- **`--dry-run`**: makes all API calls, logs what would be created, but does not write to the database.
- **`--star-limit=N`**: maximum number of stars to import per movie (default: 3).

People are always imported as a byproduct of movie imports (from the `stars` array). There is no separate people-only command.

## Default Movie IDs

30 movies bundled as a PHP constant:

```php
const DEFAULT_MOVIE_IDS = [
    'tt0053779', // La Dolce Vita
    'tt0087332', // Ghostbusters
    'tt0062622', // 2001: A Space Odyssey
    'tt0076759', // Star Wars: Episode IV - A New Hope
    'tt0060196', // The Good, the Bad and the Ugly
    'tt0047478', // Seven Samurai
    'tt0114709', // Toy Story
    'tt0050083', // 12 Angry Men
    'tt15239678', // Dune: Part Two
    'tt0043014', // Sunset Boulevard
    'tt0120737', // The Lord of the Rings: The Fellowship of the Ring
    'tt0167261', // The Lord of the Rings: The Two Towers
    'tt0167260', // The Lord of the Rings: The Return of the King
    'tt0910970', // WALL·E
    'tt6751668', // Parasite
    'tt0482571', // The Prestige
    'tt0075148', // Rocky
    'tt0021814', // City Lights
    'tt0245429', // Spirited Away
    'tt0089218', // The Goonies
    'tt0109830', // Forrest Gump
    'tt0095016', // Die Hard
    'tt0046912', // Dial M for Murder
    'tt0099685', // Goodfellas
    'tt0045152', // Singin' in the Rain
    'tt0133093', // The Matrix
    'tt0068646', // The Godfather
    'tt26733325', // Homebound
    'tt4154796', // Avengers: Endgame
    'tt0052357', // Vertigo
];
```

## API

**Base URL**: `https://api.imdbapi.dev`

No API key required. Free tier.

### Endpoints

#### Movie data

```
GET /titles/{imdb_id}
```

Response shape:

```json
{
  "id": "tt0910970",
  "primaryTitle": "WALL·E",
  "primaryImage": {
    "url": "https://m.media-amazon.com/images/...",
    "width": 1382,
    "height": 2048
  },
  "startYear": 2008,
  "runtimeSeconds": 5820,
  "genres": ["Animation", "Adventure", "Family"],
  "rating": {
    "aggregateRating": 8.4,
    "voteCount": 1195803
  },
  "plot": "In a distant future...",
  "stars": [
    {
      "id": "nm0123785",
      "displayName": "Ben Burtt",
      "primaryImage": {
        "url": "https://m.media-amazon.com/images/...",
        "width": 400,
        "height": 560
      }
    }
  ]
}
```

#### Person data

```
GET /names/{imdb_id}
```

Response shape:

```json
{
  "id": "nm0123785",
  "displayName": "Ben Burtt",
  "primaryImage": {
    "url": "https://m.media-amazon.com/images/...",
    "width": 400,
    "height": 560
  },
  "biography": "Benjamin Burtt Jr. is best known for...",
  "birthDate": {
    "year": 1948,
    "month": 7,
    "day": 12
  },
  "birthLocation": "Jamesville, New York, USA",
  "deathDate": null,
  "deathLocation": null
}
```

#### Certificates (MPA rating)

```
GET /titles/{imdb_id}/certificates
```

Response shape:

```json
{
  "certificates": [
    {
      "rating": "PG",
      "country": {
        "code": "US",
        "name": "United States"
      },
      "attributes": [
        "certificate #51192"
      ]
    }
  ]
}
```

**Extraction logic**: Find the entry where `country.code === "US"` and `attributes` contains a string matching `"certificate #"` or `"certificate#"`. Use the `rating` value. Fallback: `"Not Rated"`.

#### Videos (trailers)

```
GET /titles/{imdb_id}/videos
```

Response shape:

```json
{
  "videos": [
    {
      "id": "vi2192703769",
      "type": "trailer",
      "name": "WALL·E",
      "runtimeSeconds": 152
    },
    {
      "id": "vi1538392345",
      "type": "clip",
      "name": "Dance",
      "runtimeSeconds": 149
    }
  ]
}
```

**Extraction logic**: Filter the `videos` array for `type === "trailer"`. Take the first match's `id` field (e.g., `"vi2192703769"`). This ID is used in the IMDB embed URL: `https://www.imdb.com/video/embed/{id}/`.

If no trailers found, leave `tenup_movie_trailer_id` empty.

## Import Flow

For each movie IMDB ID:

1. **Validate** — Must match `/^tt\d{7,8}$/`. Skip and warn if invalid.
2. **Check for duplicate** — Query for existing `tenup-movie` post with `meta_key=tenup_movie_imdb_id`, `meta_value={id}`. Skip if found.
3. **Fetch movie data** — `GET /titles/{id}`
4. **Require image** — If `primaryImage` is null or missing, skip the movie and warn.
5. **Fetch certificates** — `GET /titles/{id}/certificates`
6. **Fetch videos** — `GET /titles/{id}/videos`
7. **Create post** — `wp_insert_post()` with `post_type=tenup-movie`, `post_title=primaryTitle`, `post_status=publish`.
8. **Download featured image** — From `primaryImage.url`. Filename: slugified `primaryTitle` (e.g., `wall-e.jpg`). Set as post thumbnail.
9. **Set movie meta** — All fields per the mapping table below.
10. **Assign genres** — Create `tenup-genre` terms from the `genres` array if they don't exist. Assign to the post.
11. **Import stars** — For each entry in the `stars` array (up to `--star-limit`):
    - Check if person already exists by `tenup_person_imdb_id`. If so, reuse the existing post ID (skip creation but still wire the relationship).
    - Fetch person data from `GET /names/{star.id}`.
    - If `primaryImage` is null or missing, skip the person and warn.
    - Create `tenup-person` post with `post_title=displayName`, `post_status=publish`.
    - Download featured image. Filename: slugified `displayName`.
    - Set person meta per the mapping table below.
12. **Wire relationships** — Create `movie_person` relationships between the movie post and each imported person post via Content Connect. See Relationships section below.
13. **Rate limit** — Sleep 0.5s between API requests.

## Meta Field Mapping

### Movie

| API Field | Meta Key | Conversion |
| --- | --- | --- |
| `id` | `tenup_movie_imdb_id` | string, no conversion |
| `startYear` | `tenup_movie_release_year` | cast int to string |
| `runtimeSeconds` | `tenup_movie_runtime` | convert to `{ 'hours' => string, 'minutes' => string }` object — see runtime conversion below |
| `plot` | `tenup_movie_plot` | string, no conversion |
| `rating.aggregateRating` | `tenup_movie_viewer_rating` | cast float to string |
| `rating.voteCount` | `tenup_movie_viewer_rating_count` | cast int to string |
| US certificate `rating` | `tenup_movie_mpa_rating` | from certificates endpoint; fallback `"Not Rated"` |
| First trailer video `id` | `tenup_movie_trailer_id` | from videos endpoint; IMDB `vi*` format; empty if none |

### Person

| API Field | Meta Key | Conversion |
| --- | --- | --- |
| `id` | `tenup_person_imdb_id` | string, no conversion |
| `birthDate` | `tenup_person_born` | `{ year, month, day }` → `YYYY-MM-DD` — see date conversion below |
| `birthLocation` | `tenup_person_birthplace` | string, no conversion |
| `deathDate` | `tenup_person_died` | `{ year, month, day }` → `YYYY-MM-DD`; null/empty if living |
| `deathLocation` | `tenup_person_deathplace` | string; null/empty if living |
| `biography` | `tenup_person_biography` | string, no conversion |

## Data Conversion

### Runtime

Convert `runtimeSeconds` (integer) to the object format expected by the `tenup_movie_runtime` meta field:

```php
$hours   = (string) floor( $seconds / 3600 );
$minutes = (string) floor( ( $seconds % 3600 ) / 60 );

// Result: [ 'hours' => '1', 'minutes' => '37' ]
```

### Dates

Convert the API's `{ year, month, day }` object to `YYYY-MM-DD` string:

- All three fields present and valid: `"1948-07-12"`
- Month missing or null: `"1948-01-01"` (default to January 1)
- Day missing or null: `"1948-07-01"` (default to 1st of month)
- Entire date null: store empty string / null
- Validate with `checkdate()`. Reject years before 1800 or after current year.

## Featured Image Handling

1. Download from `primaryImage.url` using WordPress's `download_url()`.
2. Generate filename: `sanitize_file_name( sanitize_title( $title ) ) . '.jpg'`. Falls back to original extension if not `.jpg`.
3. Create attachment via `wp_insert_attachment()` + `wp_generate_attachment_metadata()`.
4. Set as featured image via `set_post_thumbnail()`.
5. Requires loading admin includes:
   ```php
   require_once ABSPATH . 'wp-admin/includes/media.php';
   require_once ABSPATH . 'wp-admin/includes/file.php';
   require_once ABSPATH . 'wp-admin/includes/image.php';
   ```

## Relationships (Content Connect)

The plugin defines a `movie_person` post-to-post relationship via [Content Connect](https://github.com/10up/content-connect) in `src/Relationships.php`. After importing a movie's star cast, wire each movie→person relationship.

Content Connect stores bidirectional relationships in the `wp_post_to_post` table. The relationship is defined as:

```php
$registry->define_post_to_post(
    'tenup-movie',   // from
    'tenup-person',  // to
    'movie_person'   // relationship name
);
```

To create a relationship during import:

```php
global $wpdb;

$wpdb->insert(
    $wpdb->prefix . 'post_to_post',
    [
        'id1'   => $movie_post_id,
        'id2'   => $person_post_id,
        'name'  => 'movie_person',
        'order' => $star_index, // 0-based order from the stars array
    ]
);
```

**Duplicate prevention**: Before inserting, check if the relationship already exists:

```php
$exists = $wpdb->get_var( $wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}post_to_post WHERE id1 = %d AND id2 = %d AND name = %s",
    $movie_post_id,
    $person_post_id,
    'movie_person'
) );
```

**Order**: Maintain the order from the `stars` array (index 0, 1, 2...) so cast display matches API order.

## Duplicate Detection

Before creating any post:

- **Movies**: `WP_Query` with `meta_key=tenup_movie_imdb_id`, `meta_value={imdb_id}`, `post_type=tenup-movie`.
- **People**: `WP_Query` with `meta_key=tenup_person_imdb_id`, `meta_value={imdb_id}`, `post_type=tenup-person`.

If found, skip creation. Log as skipped. This allows the command to be run multiple times safely (idempotent).

## Dry Run

When `--dry-run` is passed:

- Make all API calls normally (to validate data availability).
- Log each movie/person that **would** be created, with title and meta values.
- Do **not** call `wp_insert_post()`, `update_post_meta()`, `download_url()`, or `set_post_thumbnail()`.
- Display a summary at the end: "Would import X movies, Y people."

## Error Handling

- **Per-ID isolation**: if one movie fails (API error, missing data), log the error and continue with the next.
- **HTTP retry**: up to 2 retries per request with 1-second backoff.
- **Rate limit response (HTTP 429)**: wait 5 seconds before retrying.
- **Request timeout**: 30 seconds.
- **Missing required fields**: skip the movie/person. Movies require `primaryTitle`, `id`, and `primaryImage`. People require `displayName`, `id`, and `primaryImage`. If `primaryImage` is null or missing, skip the entry and warn.
- **Missing optional fields**: use safe defaults — `"Not Rated"` for MPA rating, empty string for missing biography, null for missing dates.

## CLI Output

### Timing notice

Before starting the import, display an estimate so the user knows what to expect:

```
WP_CLI::log( '' );
WP_CLI::log( sprintf(
    'Importing %d movies with up to %d stars each. This will take a few minutes — sit tight.',
    $movie_count,
    $star_limit
) );
WP_CLI::log( '' );
```

### Progress bar

Use `\WP_CLI\Utils\make_progress_bar()` to show a progress bar that ticks once per movie (not per API request). This gives a clear sense of overall progress:

```php
$progress = \WP_CLI\Utils\make_progress_bar( 'Importing movies', $movie_count );

foreach ( $movie_ids as $id ) {
    // ... import logic ...
    $progress->tick();
}

$progress->finish();
```

### Log levels

- Use `WP_CLI::log()` for per-movie progress ("Importing: The Godfather...").
- Use `WP_CLI::success()` for completed items ("Created: The Godfather (3 stars)").
- Use `WP_CLI::warning()` for skipped items (duplicates, missing data).
- Use `WP_CLI::error()` (non-halting) for failed items.

### Summary

Display a final summary after the progress bar finishes:

```
WP_CLI::log( '' );
WP_CLI::success( sprintf(
    'Done! %d movies imported, %d skipped, %d failed.',
    $imported,
    $skipped,
    $failed
) );
```

## IMDB ID Validation

- Movies: `/^tt\d{7,8}$/` (prefix `tt` + 7-8 digits)
- People: `/^nm\d{7,8}$/` (prefix `nm` + 7-8 digits)

## File Structure

```
src/CLI/
├── CLIRegistration.php        # ModuleInterface — registers command when WP_CLI available
├── FueledMoviesImport.php     # WP_CLI_Command — the single command class
├── Importers/
│   ├── MovieImporter.php      # Movie post creation, meta, taxonomy, star orchestration
│   └── PersonImporter.php     # Person post creation, meta
└── Utils/
    ├── IMDBApiClient.php      # HTTP client for all 4 endpoints, caching, rate limiting
    ├── ImageManager.php       # Featured image download, attachment creation
    ├── RelationshipManager.php # Content Connect movie_person relationship wiring
    ├── Validator.php          # IMDB ID format validation, API response validation
    └── DateFormatter.php      # Date and runtime conversion helpers
```

## Registration

`CLIRegistration` implements `TenupFramework\ModuleInterface` and is auto-discovered by the framework's `ModuleInitialization`.

```php
class CLIRegistration implements ModuleInterface {

    use Module;

    public function load_order(): int {
        return 99;
    }

    public function can_register(): bool {
        return defined( 'WP_CLI' ) && WP_CLI;
    }

    public function register(): void {
        \WP_CLI::add_command( 'fueled-movies import', FueledMoviesImport::class );
    }
}
```

The command classes (`FueledMoviesImport`, importers, utils) do **not** implement `ModuleInterface` — they are plain PHP classes loaded via PSR-4 autoloading only when invoked by WP-CLI.

## Composer Dependency

Add `wp-cli/wp-cli` as a dev dependency for type hints and IDE support. It is not required at runtime (WP-CLI provides it).

```json
{
  "require-dev": {
    "wp-cli/wp-cli": "^2.0"
  }
}
```

## Performance Notes

For 30 movies with 3 stars each, worst case is ~150 API requests (30 movies × 1 title + 1 certificates + 1 videos + up to 3 people). With 0.5s rate limiting, the import takes ~75 seconds minimum. Featured image downloads add additional time depending on connection speed.

## README Update

Update `wp-content/README.md` to:

1. Add a link to the training course: `https://gutenberg.10up.com/training/Block-Based-Themes/`
2. Add a **Content Import** section after Installation with brief CLI usage:

```markdown
## Content Import

Once your site is set up, import the sample movie and person content:

\`\`\`bash
# Import the 30 default movies + their star cast
wp fueled-movies import

# Import only specific movies + their cast
wp fueled-movies import --ids=tt0910970,tt0068646

# Preview without creating posts
wp fueled-movies import --dry-run

# Override default star limit (default: 3 per movie)
wp fueled-movies import --star-limit=5
\`\`\`
```

## File Location

Once the CLI implementation is complete, move this requirements file from `mu-plugins/10up-plugin/requirements.md` to `mu-plugins/10up-plugin/src/CLI/requirements.md` to live alongside the code it documents.
