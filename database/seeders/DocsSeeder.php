<?php

namespace Database\Seeders;

use App\Models\Doc;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * DocsSeeder — Import existing Markdown files into the persistent `docs` table.
 *
 * Rationale
 *  Our documentation began life as Markdown files committed to the repository.
 *  The product now persists docs in the database so they can be edited via the
 *  super-admin dashboard. This seeder performs a one-time (idempotent) import
 *  from a filesystem directory into the `docs` table by upserting rows keyed
 *  by `slug`.
 *
 * Behavior
 *  - Determines an import directory from the environment variable
 *    DOCS_FS_IMPORT_PATH, falling back to sensible local defaults.
 *  - Scans for *.md files and, for each file:
 *      - Derives a slug from the filename.
 *      - Extracts the first `# Heading` line as the title, or generates a
 *        human title from the filename when absent.
 *      - Upserts into the `docs` table: { slug, title, body }.
 *  - Safe to run multiple times; it will update existing rows in non-production
 *    but will NOT overwrite existing docs in production (create-only).
 *
 * Production Safety
 *  - In production, this seeder only creates missing docs and never overwrites
 *    existing database content to preserve admin edits made in-app.
 *  - Leave DOCS_FS_IMPORT_PATH unset to use built-in fallbacks such as
 *    resources/docs bundled into the container image.
 */
class DocsSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Resolve the import directory.
        $path = (string) env('DOCS_FS_IMPORT_PATH', '');

        if ($path === '') {
            // Fallbacks for local development. We try a few likely paths.
            $candidates = [
                base_path('../ofroot-frontend-application/docs'), // sibling frontend repo (dev only)
                base_path('resources/docs'),                       // backend-bundled docs (prod-safe)
                base_path('docs'),                                 // root docs
                base_path('../docs'),                              // parent docs
            ];
            foreach ($candidates as $candidate) {
                if (is_dir($candidate)) {
                    $path = realpath($candidate) ?: $candidate;
                    break;
                }
            }
        }

        if ($path === '' || !is_dir($path)) {
            $this->command?->warn('DocsSeeder: No import path found; skipping. Set DOCS_FS_IMPORT_PATH to enable.');
            return;
        }

        // 2) Enumerate Markdown files.
        $files = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.md') ?: [];
        if (empty($files)) {
            $this->command?->warn("DocsSeeder: No .md files found in {$path}; nothing to import.");
            return;
        }

        $isProd = App::environment('production');

        // 3) Import each file.
        foreach ($files as $file) {
            $contents = @file_get_contents($file);
            if ($contents === false) {
                $this->command?->warn("DocsSeeder: Unable to read {$file}; skipping.");
                continue;
            }

            // Derive slug from filename (lowercase, hyphens, alnum-only plus dashes).
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $slug = Str::of($filename)
                ->lower()
                ->replace([' ', '_'], '-')
                ->replaceMatches('/[^a-z0-9\-]/', '')
                ->trim('-')
                ->value();
            if ($slug === '') {
                $this->command?->warn("DocsSeeder: Could not derive slug for {$file}; skipping.");
                continue;
            }

            // Extract the first level-1 heading as the title.
            $title = null;
            if (preg_match('/^#\s+(.+)$/m', $contents, $m)) {
                $title = trim($m[1]);
            }
            if (!$title) {
                $title = Str::of($filename)->headline()->value();
            }

            // In production, preserve any existing doc (create-only); otherwise update.
            if ($isProd) {
                $existing = Doc::where('slug', $slug)->first();
                if ($existing) {
                    $this->command?->info("DocsSeeder: existing doc '{$slug}' present; leaving unchanged (prod safe).");
                } else {
                    Doc::create(['slug' => $slug, 'title' => $title, 'body' => $contents]);
                    $this->command?->info("DocsSeeder: created '{$slug}' from '{$file}'.");
                }
            } else {
                Doc::updateOrCreate(
                    ['slug' => $slug],
                    ['title' => $title, 'body' => $contents]
                );
                $this->command?->info("DocsSeeder: upserted '{$slug}' from '{$file}'.");
            }
        }

        $this->command?->info('DocsSeeder: Import complete.');
    }
}
