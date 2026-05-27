# AGENT SKILL SUGGESTION #1: Shared Entry Point Pattern

Optimize bundle sizes by creating a shared entry point for common dependencies used across multiple blocks or scripts.

## Status: Reverted (training repo simplification)

This optimization was **implemented experimentally** (shared bundle + webpack externals + PHP dependency injection) and then **reverted**.

### Why we reverted it

- **Build/runtime coupling**: once externals are enabled, the editor and block scripts must load in a very specific order, and stale `dist/` artifacts can easily break the site (e.g. `tenupSharedComponents is not defined`).
- **Higher complexity for training**: it introduces webpack “issuer” logic and global dependency patterns that are *not necessary* to teach Full Site Editing fundamentals.
- **Harder debugging**: failures show up as runtime globals missing rather than straightforward import/bundle behavior.

### What was undone

The following pieces were removed so the theme returns to the default 10up-toolkit behavior (each bundle includes its non-WordPress dependencies):

- **Source entry**: `assets/js/shared-components.js`
- **Toolkit entry config**: `package.json` `"10up-toolkit.entry.shared-components"`
- **Custom externals config**: `webpack.config.js`
- **PHP wiring**:
  - removed `tenup-shared-components` enqueue in `src/Assets.php`
  - removed dependency injection into block editor scripts in `src/Blocks.php`
- **Dist artifacts**: `dist/js/shared-components.*` removed after rebuild

### If we ever want this back

This pattern can be reintroduced later as an advanced/performance module once the training covers:
- webpack overrides in the 10up-toolkit ecosystem
- script load order in the editor vs frontend
- debugging externals/global variables

## The Problem

When multiple blocks import the same libraries (like `@10up/block-components`), each block bundles its own copy:

```
dist/
├── blocks/
│   ├── block-a/index.js    → 287 KiB (includes @10up/block-components)
│   ├── block-b/index.js    → 287 KiB (includes @10up/block-components)
│   └── block-c/index.js    → 287 KiB (includes @10up/block-components)
```

Total: ~861 KiB of duplicated code.

## The Solution

Create a shared entry point that bundles common dependencies once, then configure webpack to externalize those dependencies in other bundles.

### Step 1: Create Shared Entry Point

```javascript
// assets/js/shared-components.js

import * as blockComponents from '@10up/block-components';
import clsx from 'clsx';

// Expose shared components globally.
// This allows other modules to use them without re-bundling.
window.tenupSharedComponents = {
    ...blockComponents,
    clsx,
};
```

### Step 2: Add Entry to package.json

```json
{
  "10up-toolkit": {
    "entry": {
      "shared-components": "./assets/js/shared-components.js",
      "block-extensions": "./assets/js/block-extensions.js"
    }
  }
}
```

### Step 3: Configure Webpack Externals

Create `webpack.config.js` to externalize the shared dependencies:

```javascript
// webpack.config.js

const path = require('path');
const defaultConfigs = require('10up-toolkit/config/webpack.config');

// Absolute path to shared-components source file.
const sharedComponentsPath = path.resolve(__dirname, 'assets/js/shared-components.js');

// Custom external function to externalize dependencies except for shared-components entry.
const customExternalFn = ({ context, request, contextInfo }, callback) => {
    // Get the issuer (file that's doing the import).
    const issuer = contextInfo?.issuer || '';

    // Don't externalize if the issuer is shared-components.js itself.
    if (issuer === sharedComponentsPath || issuer.endsWith('shared-components.js')) {
        return callback();
    }

    // Don't externalize internal imports from within the package.
    if (context && context.includes('@10up/block-components')) {
        return callback();
    }

    // Externalize @10up/block-components to use the global.
    if (request === '@10up/block-components') {
        return callback(null, 'tenupSharedComponents');
    }

    // Externalize clsx as a property of the global.
    if (request === 'clsx') {
        return callback(null, ['tenupSharedComponents', 'clsx']);
    }

    return callback();
};

// 10up-toolkit returns an array of webpack configs.
module.exports = defaultConfigs.map((config) => {
    const existingExternals = config.externals || [];
    const externalsArray = Array.isArray(existingExternals)
        ? existingExternals
        : [existingExternals].filter(Boolean);

    return {
        ...config,
        externals: [...externalsArray, customExternalFn],
    };
});
```

### Step 4: Enqueue Shared Script in PHP

The shared script must load before any scripts that depend on it:

```php
// Assets.php or functions.php

public function enqueue_block_editor_assets(): void {
    // Enqueue shared components first.
    // Must load in header (false) to ensure it's available before block scripts.
    wp_enqueue_script(
        'tenup-shared-components',
        YOUR_THEME_URL . '/dist/js/shared-components.js',
        $this->get_asset_info( 'shared-components', 'dependencies' ),
        $this->get_asset_info( 'shared-components', 'version' ),
        false // Load in header, not footer
    );

    // Other editor scripts depend on shared-components.
    wp_enqueue_script(
        'tenup-theme-block-extensions',
        YOUR_THEME_URL . '/dist/js/block-extensions.js',
        array_merge(
            [ 'tenup-shared-components' ],
            $this->get_asset_info( 'block-extensions', 'dependencies' ) ?? []
        ),
        $this->get_asset_info( 'block-extensions', 'version' ),
        true
    );
}
```

### Step 5: Add Dependency to Block Scripts

Blocks registered via `block.json` need the shared script as a dependency:

```php
// Blocks.php

public function register_blocks(): void {
    $block_json_files = glob( YOUR_BLOCKS_DIR . '*/block.json' );

    foreach ( $block_json_files as $block_json ) {
        $block = register_block_type( dirname( $block_json ) );

        // Inject shared-components as a dependency for block editor scripts.
        if ( ! empty( $block->editor_script_handles ) ) {
            foreach ( $block->editor_script_handles as $handle ) {
                $script = wp_scripts()->query( $handle );
                if ( $script && ! in_array( 'tenup-shared-components', $script->deps, true ) ) {
                    $script->deps[] = 'tenup-shared-components';
                }
            }
        }
    }
}
```

## Result

After implementing this pattern:

```
dist/
├── js/
│   └── shared-components.js  → 287 KiB (includes @10up/block-components)
├── blocks/
│   ├── block-a/index.js      → 1.5 KiB (references global)
│   ├── block-b/index.js      → 1.8 KiB (references global)
│   └── block-c/index.js      → 1.2 KiB (references global)
```

Total: ~291 KiB (vs ~861 KiB) — **66% reduction**.

## Which Dependencies to Share

Good candidates for the shared bundle:

| Package | Size Impact | Notes |
|---------|-------------|-------|
| `@10up/block-components` | ~280 KiB | Used by most custom blocks |
| `clsx` | ~1 KiB | Common utility |
| `classnames` | ~1 KiB | Alternative to clsx |
| Custom component libraries | Varies | If used in 3+ blocks |

**Don't share:**
- WordPress packages (`@wordpress/*`) — already externalized by 10up-toolkit
- Small utilities used in only 1-2 places

## Verification

After building, check bundle sizes:

```bash
# List all bundle sizes
ls -lh dist/js/*.js dist/blocks/*/index.js

# Verify shared-components contains the libraries
grep -l "block-components" dist/js/shared-components.js

# Verify individual blocks reference the global
grep "tenupSharedComponents" dist/blocks/*/index.js
```

## Troubleshooting

### `tenupSharedComponents is not defined`

1. **shared-components.js is externalizing itself**
   - Check webpack.config.js issuer detection includes the full path
   - Add console.log to debug: `console.log('issuer:', issuer)`

2. **Script loading order**
   - Ensure shared-components loads in header (`in_footer: false`)
   - Verify it's listed as a dependency for consuming scripts

3. **Missing dependency injection**
   - Confirm block scripts have `tenup-shared-components` in their deps array
   - Use Query Monitor to check script dependencies

### Bundle size didn't decrease

1. **Externals not applied**
   - Check webpack.config.js is being loaded (add a console.log)
   - Verify the request string matches exactly (case-sensitive)

2. **Tree shaking not working**
   - Import specific exports: `import { PostMeta } from '@10up/block-components'`
   - Check package has `"sideEffects": false` in its package.json

## When to Use This Pattern

✅ **Use when:**
- 3+ blocks/scripts share the same large dependency
- Total duplicate code exceeds ~200 KiB
- Build output exceeds 500 KiB

❌ **Don't use when:**
- Only 1-2 scripts use the dependency
- The dependency is small (<20 KiB)
- Scripts are loaded on different pages (no overlap)
