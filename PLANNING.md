# Fueled Movies Theme - Codebase Evaluation & Improvement Plan

This document evaluates the `fueled-movies` theme against 10up engineering best practices and the `.cursor/rules` skills, identifying areas for improvement and providing actionable steps.

---

## Executive Summary

The theme is **well-structured** and follows many 10up conventions:
- ✅ Uses `10up/wp-framework` for modular architecture
- ✅ Implements `ModuleInterface` pattern correctly
- ✅ Uses `10up-toolkit` for builds
- ✅ Dynamic blocks with PHP rendering
- ✅ Proper `theme.json` configuration
- ✅ Uses `@10up/block-components`

**Key areas needing attention:**
1. Build warnings (deprecated icon imports)
2. Bundle size optimization
3. Missing `TemplateTags` module implementation
4. Incomplete block structure standardization
5. CSS organization improvements

---

## 1. Critical Issues

### 1.1 Deprecated Icon Import (`externalLink`)

**Problem:** Build warnings indicate `externalLink` is not exported from `@wordpress/icons`.

**Files Affected:**
- `assets/js/block-components/PostMeta/MovieIMDBID.js`
- `assets/js/block-components/PostMeta/MovieYouTubeID.js`
- `assets/js/block-components/PostMeta/PersonIMDBID.js`

**Solution:** Replace `externalLink` with `external` (the correct export name).

```javascript
// Before
import { externalLink } from '@wordpress/icons';

// After
import { external } from '@wordpress/icons';
```

**Action Steps:**
1. [ ] Update imports in all three files
2. [ ] Update usage from `icon={externalLink}` to `icon={external}`
3. [ ] Rebuild and verify warnings are resolved

---

### 1.2 Bundle Size Optimization

**Problem:** Multiple block bundles are ~287 KiB each, exceeding the 100 KiB recommendation.

**Root Cause:** Each block independently bundles shared dependencies.

**Solution Options:**

#### Option A: Shared Entry Point (Recommended)
Create a shared script that all blocks depend on:

```json
// package.json - 10up-toolkit config
{
  "10up-toolkit": {
    "entry": {
      "shared-components": "./assets/js/shared-components.js"
    }
  }
}
```

```javascript
// assets/js/shared-components.js
export * from '@10up/block-components';
export * from '@wordpress/components';
// ... other shared imports
```

#### Option B: Lazy Loading
Use dynamic imports for non-critical components:

```javascript
const { Spinner } = await import('@wordpress/components');
```

**Action Steps:**
1. [ ] Analyze shared dependencies across blocks
2. [ ] Create shared entry point for common code
3. [ ] Update blocks to import from shared bundle
4. [ ] Measure bundle size reduction

---

## 2. Framework Compliance

### 2.1 `ThemeCore.php` - Not Implementing `ModuleInterface`

**Current State:** `ThemeCore.php` is a standalone class, not a module.

**Assessment:** This is **correct** per 10up patterns. `ThemeCore` is the bootstrap class that initializes modules, not a module itself.

**Status:** ✅ No changes needed

---

### 2.2 `TemplateTags.php` - Empty Module

**Current State:** The class exists but has no implementation.

**Issue:** If this is meant to be a module, it should implement `ModuleInterface`. Currently it's just a namespace placeholder.

**Options:**

#### Option A: Remove if Not Needed
If template tags are defined in `template-tags.php` as pure functions, this class may be unnecessary.

#### Option B: Implement as Proper Module
If it should register functionality:

```php
<?php
namespace FueledMoviesTheme;

use TenupFramework\Module;
use TenupFramework\ModuleInterface;

class TemplateTags implements ModuleInterface {
    use Module;

    public function can_register(): bool {
        return true;
    }

    public function register(): void {
        // Register any hooks related to template tags
    }
}
```

**Action Steps:**
1. [ ] Determine if `TemplateTags.php` is needed
2. [ ] Either delete or implement as proper `ModuleInterface`
3. [ ] Update autoload if file is removed

---

## 3. Block Development Improvements

### 3.1 Block Structure Standardization

**Current Structure:**
```
blocks/
├── movie-metadata-genre/
│   ├── block.json
│   ├── edit.js
│   ├── index.js
│   └── markup.php
```

**10up Standard Additions:**
```
blocks/
├── movie-metadata-genre/
│   ├── block.json
│   ├── edit.js
│   ├── index.js
│   ├── markup.php
│   ├── save.js          ← Missing (should return null for dynamic blocks)
│   └── style.css        ← Optional but recommended for block-specific styles
```

**Action Steps:**
1. [ ] Add `save.js` to each block (return null for dynamic blocks)
2. [ ] Consider adding block-specific CSS files where needed

---

### 3.2 Block Edit Components - Missing `useBlockProps`

**Current State:** Block edit components don't use `useBlockProps`.

**Example - `movie-metadata-genre/edit.js`:**
```javascript
// Current - no wrapper with block props
return (
    <>
        <dt>{__('Genre', 'tenup')}</dt>
        <dd>...</dd>
    </>
);
```

**Recommended Pattern:**
```javascript
import { useBlockProps } from '@wordpress/block-editor';

export const BlockEdit = () => {
    const blockProps = useBlockProps();
    // ...
    return (
        <dl {...blockProps}>
            <dt>{__('Genre', 'tenup')}</dt>
            <dd>...</dd>
        </dl>
    );
};
```

**Note:** This may be intentional if blocks are children of a parent block with its own wrapper.

**Action Steps:**
1. [ ] Evaluate if blocks should have their own wrapper
2. [ ] Add `useBlockProps` where appropriate
3. [ ] Ensure PHP markup uses `get_block_wrapper_attributes()` to match

---

### 3.3 Block Markup PHP - Missing Wrapper Attributes

**Current State:** `markup.php` files don't use `get_block_wrapper_attributes()`.

**Current (`movie-metadata-genre/markup.php`):**
```php
<dt><?php echo esc_html( Genre::PLURAL_LABEL ); ?></dt>
<dd>...</dd>
```

**10up Standard:**
```php
$wrapper_attributes = get_block_wrapper_attributes();
?>
<dl <?php echo $wrapper_attributes; ?>>
    <dt><?php echo esc_html( Genre::PLURAL_LABEL ); ?></dt>
    <dd>...</dd>
</dl>
```

**Action Steps:**
1. [ ] Audit all `markup.php` files
2. [ ] Add wrapper elements with `get_block_wrapper_attributes()` where appropriate
3. [ ] Update corresponding `edit.js` to match structure

---

## 4. Theme.json Improvements

### 4.1 Version 3 Compliance

**Current:** Using version 3 ✅

**Missing Recommended Settings:**

```json
{
  "settings": {
    "blocks": {
      "core/button": {
        "color": {
          "custom": false
        }
      }
    }
  }
}
```

**Action Steps:**
1. [ ] Review block-specific settings needed
2. [ ] Add constraints for core blocks as needed

---

### 4.2 Typography - Limited Font Families

**Current:** Only system font defined.

**Consideration:** If custom fonts are needed, add them:

```json
{
  "settings": {
    "typography": {
      "fontFamilies": [
        {
          "fontFamily": "var(--font-primary)",
          "name": "Primary",
          "slug": "primary",
          "fontFace": [
            {
              "fontFamily": "CustomFont",
              "fontWeight": "400",
              "fontStyle": "normal",
              "src": ["file:./assets/fonts/custom-font.woff2"]
            }
          ]
        }
      ]
    }
  }
}
```

---

## 5. CSS Organization

### 5.1 Current Structure

```
assets/css/
├── base/
├── blocks/
├── components/
├── editor-canvas-style-overrides.css
├── editor-frame-style-overrides.css
├── mixins/
├── templates/
└── utilities/
```

**Assessment:** Good organization ✅

### 5.2 Suggested Improvements

#### Add Block-Specific Styles in Block Folders
Move block CSS closer to block logic:

```
blocks/
├── movie-metadata-genre/
│   ├── block.json
│   ├── edit.js
│   ├── index.js
│   ├── markup.php
│   └── style.css  ← Block-specific styles here
```

**Update `block.json`:**
```json
{
  "style": "file:./style.css",
  "editorStyle": "file:./editor.css"
}
```

---

## 6. Block Extensions Review

### 6.1 Current Implementation

**File:** `assets/js/block-extensions.js`
```javascript
import './block-filters';
import './block-plugins';
import './block-styles';
```

**Assessment:** ✅ Good modular organization

### 6.2 Block Filters - Using `@10up/block-components`?

**Check:** Verify if `registerBlockExtension` from `@10up/block-components` is being used.

**Action Steps:**
1. [ ] Review `block-filters/group.js` implementation
2. [ ] Consider migrating to `registerBlockExtension` if using manual filters
3. [ ] Document block extensions for future maintenance

---

## 7. Performance Considerations

### 7.1 Block Editor Assets

**Current Pattern:**
- `block-extensions.js` loaded for editor
- Block scripts auto-loaded by 10up-toolkit

**Potential Improvement:** Lazy load extensions not needed on initial load.

### 7.2 Query Optimization

**In `Blocks.php`:**
```php
$block_json_files = glob( FUELED_MOVIES_THEME_BLOCK_DIST_DIR . '*/block.json' );
```

**Consideration:** Cache this result in a transient if block count grows large.

---

## 8. Immediate Action Items (Priority Order)

### High Priority
1. [ ] **Fix `externalLink` icon imports** - Build warnings
2. [ ] **Optimize bundle sizes** - Performance impact

### Medium Priority
3. [ ] **Add `save.js` files** - Block completeness
4. [ ] **Review `useBlockProps` usage** - Editor/frontend parity
5. [ ] **Implement or remove `TemplateTags.php`** - Clean architecture

### Low Priority
6. [ ] **Add block-specific CSS files** - Maintainability
7. [ ] **Document block extensions** - Team knowledge
8. [ ] **Review theme.json for block settings** - Consistency

---

## 9. Testing Checklist

After implementing changes:

- [ ] Run `npm run build` - No warnings
- [ ] Check bundle sizes in `dist/` - Under 100 KiB per block
- [ ] Test all blocks in editor - No console errors
- [ ] Verify frontend rendering matches editor
- [ ] Run `composer phpcs` - No violations
- [ ] Run `npm run lint-js` - No violations
- [ ] Run `npm run lint-style` - No violations

---

## 10. Resources

- [10up Block Development Skill](.cursor/rules/10up-block-development/RULE.mdc)
- [10up Block Themes Skill](.cursor/rules/10up-block-themes/RULE.mdc)
- [10up WP Framework Skill](.cursor/rules/10up-wp-framework/RULE.mdc)
- [10up Toolkit Skill](.cursor/rules/10up-toolkit/RULE.mdc)
- [10up Block Extensions Skill](.cursor/rules/10up-block-extensions/RULE.mdc)
- [10up Performance Skill](.cursor/rules/10up-performance/RULE.mdc)
