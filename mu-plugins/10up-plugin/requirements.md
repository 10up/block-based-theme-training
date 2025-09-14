# IMDB Import CLI Script Requirements

## WP CLI Commands

### Movie Import
```bash
wp imdb-import movies <imdb_ids>... [options]
```
Import movies by IMDB IDs with optional star cast import.

### Person Import
```bash
wp imdb-import people <imdb_ids>... [options]
```
Import people by IMDB IDs.

### Bulk Updates
```bash
wp imdb-import update-movies [--batch-size=10] [--dry-run]
wp imdb-import update-people [--batch-size=10] [--dry-run]
wp imdb-import update-all [--batch-size=10] [--dry-run]
```
Update existing posts with fresh API data.

### Options
- `--dry-run`: Preview without creating/updating posts
- `--import-stars`: Import star cast (movies only, default: true)
- `--skip-stars`: Skip star cast import
- `--stars-limit=N`: Limit stars per movie (default: 3)
- `--force-update`: Force update even if data unchanged
- `--skip-image-update`: Skip featured image replacement
- `--preserve-relationships`: Don't update star relationships
- `--post-status=STATUS`: Set post status (default: publish)
- `--file=FILE`: Read IMDB IDs from file

## Overview
Create a WP CLI script that can take a list of IMDB IDs and import movie and person data into WordPress posts.

## API Endpoints
- **Movies**: `https://api.imdbapi.dev/titles/{imdb_id}`
- **People**: `https://api.imdbapi.dev/names/{imdb_id}`

## Movie Import Requirements

### Post Creation/Update
- **Post Type**: `tenup-movie`
- **Post Title**: `primaryTitle` from API response
- **Post Status**: `publish` (or configurable)
- **Update Behavior**: If post exists (by `tenup_movie_imdb_id`), update with latest data

### Movie Meta Fields
Based on existing post meta fields in the 10up-plugin, map the following API response fields:

| API Field | Meta Key | Description | Data Type |
|-----------|----------|-------------|-----------|
| `id` | `tenup_movie_imdb_id` | IMDB ID | string |
| `startYear` | `tenup_movie_release_year` | Release year | string |
| `runtimeSeconds` | `tenup_movie_runtime` | Runtime in hours/minutes format | object |
| `plot` | `tenup_movie_plot` | Movie plot | string |
| `rating.aggregateRating` | `tenup_movie_viewer_rating` | Viewer rating | string |
| `rating.voteCount` | `tenup_movie_viewer_rating_count` | Number of votes | string |

### Runtime Conversion
Convert `runtimeSeconds` to the expected object format:
```php
[
    'hours' => 'X',
    'minutes' => 'Y'
]
```

### Genre Taxonomy
- **Taxonomy**: `tenup-genre`
- **Source**: `genres` array from API response
- Create terms if they don't exist

### Featured Image
- **Source**: `primaryImage.url` from API response
- Download and set as featured image if URL is available
- Replace the image file name with a slugified version of the movie title
- **Update Behavior**: For existing posts, delete old featured image and replace with new one

### Star Cast Import
- **Source**: `stars` array from API response
- For each star in the array:
  - Create a new `tenup-person` post
  - Set post title to `displayName`
  - Store `id` as `tenup_person_imdb_id` meta field
  - Download `primaryImage.url` as featured image with slugified display name
  - Make additional API call to People endpoint using the star's IMDB ID
  - Populate additional person meta fields from People API response
  - Create `movie_person` relationship between movie and person posts

### Relationship Management
- **Relationship Type**: `movie_person` (as defined in Relationships.php)
- **Storage**: Store relationships in `wp_post_to_post` database table
- **Bidirectional**: Create relationships in both directions (movie→person and person→movie)
- **Order Field**: Maintain order of stars as they appear in the API response
- **Duplicate Prevention**: Check for existing relationships before creating new ones

## Person Import Requirements

### Post Creation/Update
- **Post Type**: `tenup-person`
- **Post Title**: `displayName` from API response
- **Post Status**: `publish` (or configurable)
- **Update Behavior**: If post exists (by `tenup_person_imdb_id`), update with latest data

### Person Meta Fields
Based on existing post meta fields in the 10up-plugin, map the following API response fields:

| API Field | Meta Key | Description | Data Type |
|-----------|----------|-------------|-----------|
| `id` | `tenup_person_imdb_id` | IMDB ID | string |
| `birthDate` | `tenup_person_born` | Full birth date | string |
| `birthLocation` | `tenup_person_birthplace` | Birthplace | string |
| `deathDate` | `tenup_person_died` | Full death date | string |
| `deathLocation` | `tenup_person_deathplace` | Death place | string |
| `biography` | `tenup_person_biography` | Biography | string |

### Date Handling
- **Birth Date**: Format `birthDate` as `YYYY-MM-DD` for `tenup_person_born`
- **Death Date**: Format `deathDate` as `YYYY-MM-DD` for `tenup_person_died`
- Handle cases where dates might be incomplete (missing month/day)
- If only year is available, use `YYYY-01-01` format
- If year and month are available, use `YYYY-MM-01` format

### Death Information
- **Source**: `deathDate` and `deathLocation` from API response
- **Note**: Not all people will have death information (living people)
- Only populate death fields if `deathDate` exists in the API response

### Featured Image
- **Source**: `primaryImage.url` from API response
- Download and set as featured image if URL is available
- Replace the image file name with a slugified version of the persons name
- **Update Behavior**: For existing posts, delete old featured image and replace with new one

## Update Behavior for Existing Posts

### Post Detection
- **Movies**: Check for existing posts by `tenup_movie_imdb_id` meta field
- **People**: Check for existing posts by `tenup_person_imdb_id` meta field
- **Default Action**: Update existing posts with latest API data

### Data Update Process
1. **Fetch Latest Data**: Get fresh data from IMDB API
2. **Update Post Fields**: Update title, content, and all meta fields
3. **Update Taxonomies**: Refresh genre terms and other taxonomies
4. **Replace Featured Image**: Delete old image and download new one
5. **Update Relationships**: Refresh star cast relationships

### Featured Image Replacement
- **Detection**: Check if current featured image differs from API response
- **Deletion**: Remove old featured image from media library
- **Download**: Download new image from `primaryImage.url`
- **Filename**: Use slugified title/name for new filename
- **Attachment**: Set new image as featured image
- **Cleanup**: Remove old image file from filesystem

### Star Cast Updates
- **Existing Stars**: Update existing person posts with latest data
- **New Stars**: Create new person posts for new cast members
- **Removed Stars**: Optionally remove relationships for stars no longer in cast
- **Order Update**: Update star order based on latest API response

### Update Options
- `--force-update`: Force update even if data appears unchanged
- `--skip-image-update`: Skip featured image replacement
- `--preserve-relationships`: Don't update existing star relationships

## CLI Script Features

### Command Structure
```bash
wp imdb-import movies tt0910970 tt1234567
wp imdb-import people nm0123785 nm1234567
wp imdb-import both --file=imdb_ids.txt
```

### Star Import Options
- `--import-stars`: Import star cast when importing movies (default: true)
- `--skip-stars`: Skip star cast import when importing movies
- `--stars-limit`: Maximum number of stars to import per movie (default: 3)

### Options
- `--dry-run`: Preview what would be imported without creating posts
- `--update`: Update existing posts if they already exist (default behavior)
- `--skip-existing`: Skip posts that already exist
- `--force-update`: Force update even if data appears unchanged
- `--skip-image-update`: Skip featured image replacement during updates
- `--preserve-relationships`: Don't update existing star relationships
- `--post-status`: Set post status (default: publish)
- `--file`: Read IMDB IDs from a file (one per line)

### Error Handling
- Handle API rate limits and timeouts
- Log failed imports with reasons
- Continue processing remaining IDs if some fail
- Validate IMDB ID format before API calls

### Logging
- Provide progress indicators
- Log successful imports
- Log errors with detailed messages
- Option to save log to file

## Data Validation

### IMDB ID Format
- Movies: `tt` followed by 7-8 digits
- People: `nm` followed by 7-8 digits

### Required Fields
- Movies must have `primaryTitle`
- People must have `displayName`
- Skip entries missing required fields

## Performance Considerations
- Batch API requests where possible
- Implement rate limiting to respect API limits
- Use WordPress transients for caching API responses
- Process large lists in chunks to avoid memory issues

## Star Cast Import Process

### Movie Import with Stars
When importing a movie, the script should:

1. **Fetch Movie Data**: Get movie data from IMDB API
2. **Create Movie Post**: Create the movie post with all meta fields
3. **Process Stars Array**: For each star in the `stars` array:
   - Check if person already exists (by `tenup_person_imdb_id`)
   - If exists:
     - Update person post with latest data from People API
     - Replace featured image if different from API response
     - Create/update relationship
   - If not exists, create new person post:
     - Set title to `displayName`
     - Store `id` as `tenup_person_imdb_id`
     - Download `primaryImage.url` with slugified filename
     - Make People API call for additional data
     - Populate all person meta fields
   - Create `movie_person` relationship in both directions
   - Maintain star order from API response

### Image Filename Handling
- **Movie Images**: `{movie-title-slug}.jpg`
- **Person Images**: `{person-name-slug}.jpg`
- Use WordPress `sanitize_file_name()` function
- Handle duplicate filenames by appending numbers

### API Rate Limiting
- Respect rate limits between movie and people API calls
- Cache people API responses to avoid duplicate calls
- Batch process stars to minimize API requests

## Additional Notes

### Fields Not Available in API
The following existing post meta fields do not have equivalents in the IMDB API responses and will not be populated:
- `tenup_movie_mpa_rating` (MPA rating not available)
- `tenup_movie_summary` (summary not available)
- `tenup_movie_synopsis` (synopsis not available)
- `tenup_movie_tagline` (tagline not available)

### Missing Meta Fields
The following fields from the API responses don't have corresponding post meta fields in the plugin:
- Movie: `metacritic.score`, `metacritic.reviewCount`, `directors`, `writers`, `stars`, `originCountries`, `spokenLanguages`
- Person: `alternativeNames`, `primaryProfessions`, `heightCm` (needs new meta field), `deathReason` (cause of death)

### Additional API Data Available
The API also provides additional information that could be useful:
- **Person**: `birthName` (full birth name), `deathReason` (cause of death), `primaryProfessions` (array of professions)
- **Movie**: `metacritic` scores, detailed cast/crew information, country/language data

## Bulk Update Requirements

### Update All Existing Posts
Add functionality to update all existing movie and person posts with current data from the IMDB API.

#### Update Commands
```bash
wp imdb-import update-movies [--batch-size=10] [--dry-run]
wp imdb-import update-people [--batch-size=10] [--dry-run]
wp imdb-import update-all [--batch-size=10] [--dry-run]
```

#### Update Options
- `--batch-size`: Number of posts to process in each batch (default: 10)
- `--dry-run`: Preview what would be updated without making changes
- `--force`: Force update even if data appears unchanged
- `--skip-errors`: Continue processing if individual posts fail
- `--limit`: Maximum number of posts to update (useful for testing)

#### Update Behavior
- **Movies**: Update all posts with `tenup_movie_imdb_id` meta field
- **People**: Update all posts with `tenup_person_imdb_id` meta field
- **Batch Processing**: Process posts in configurable batches to avoid memory issues
- **Progress Tracking**: Show progress bar and current post being processed
- **Error Handling**: Log failed updates and continue with remaining posts
- **Data Comparison**: Only update if API data differs from current post data (unless `--force` is used)

#### Update Fields
For each post type, update the following fields from fresh API data:

**Movies:**
- Post title (if different)
- All meta fields (release year, runtime, plot, ratings, etc.)
- Genre taxonomy terms

**People:**
- Post title (if different)
- All meta fields (birth/death dates, birthplace, biography, etc.)

#### Performance Considerations
- Use WordPress transients to cache API responses during batch processing
- Implement memory management for large datasets
- Add progress indicators for long-running operations
- Provide option to resume interrupted updates
- Log processing statistics (updated, skipped, failed counts)

#### Safety Features
- Backup recommendation before running updates
- Confirmation prompt for large update operations
- Rollback capability for failed updates
- Detailed logging of all changes made

## Sample IMDB IDs for Testing

### Test Movie IMDB IDs
Use these IMDB IDs for testing the CLI script (from current database):

| Post ID | IMDB ID | Movie Title |
|---------|---------|-------------|
| 675 | `tt0088847` | The Breakfast Club |
| 524 | `tt0118799` | Life Is Beautiful |
| 243 | `tt0080684` | Star Wars: Episode V - The Empire Strikes Back |
| 231 | `tt0060196` | The Good, the Bad and the Ugly |
| 251 | `tt0047478` | Seven Samurai |
| 657 | `tt0087332` | Ghostbusters |
| 69 | `tt4154796` | Avengers: Endgame |
| 67 | `tt2380307` | Coco |
| 225 | `tt0050083` | 12 Angry Men |
| 14 | `tt15239678` | Dune: Part Two |
| 533 | `tt0043014` | Sunset Boulevard |
| 233 | `tt0120737` | The Lord of the Rings: The Fellowship of the Ring |
| 241 | `tt0167261` | The Lord of the Rings: The Two Towers |
| 53 | `tt0167260` | The Lord of the Rings: The Return of the King |
| 65 | `tt0910970` | WALL·E |
| 227 | `tt0108052` | Schindler's List |
| 197 | `tt0111161` | The Shawshank Redemption |
| 229 | `tt0110912` | Pulp Fiction |
| 239 | `tt1375666` | Inception |
| 63 | `tt0245429` | Spirited Away |
| 514 | `tt0112950` | Empire Records |
| 237 | `tt0109830` | Forrest Gump |
| 504 | `tt5164214` | Ocean's Eight |
| 221 | `tt0468569` | The Dark Knight |
| 247 | `tt0099685` | Goodfellas |
| 543 | `tt0045152` | Singin' in the Rain |
| 245 | `tt0133093` | The Matrix |
| 219 | `tt0068646` | The Godfather |
| 223 | `tt0071562` | The Godfather Part II |

### Sample People IMDB IDs
| Name | IMDB ID |
|------|---------|
| Marlon Brando | `nm0000008` |
| Ben Burtt | `nm0123785` |

## Updating all posts
