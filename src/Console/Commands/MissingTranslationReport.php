<?php

namespace Shepp\LaravelTranslationReport\Console\Commands;

use Illuminate\Console\Command;

class MissingTranslationReport extends Command
{
    /** @var string */
    protected $signature = "translations:report {--exclude=*}";

    /** @var string */
    protected $description = "Output a CSV report of missing translations";

    /**
     * @return int
     */
    public function handle(): int
    {
        $excludedFiles = $this->option('exclude') ?: [];
        $baseDir = $this->baseDir();
        $languages = $this->languages($baseDir);

        fputcsv(STDOUT, ['language', 'key', 'example_language', 'example']);

        foreach ($languages as $language) {
            $files = $this->translationFiles($baseDir, $language);

            foreach ($files as $file) {
                // Skip any excluded files
                if (in_array(basename($file), $excludedFiles)) {
                    continue;
                }

                $keys = $this->keys($file);

                foreach ($languages as $compareLang) {
                    if ($compareLang === $language) {
                        continue;
                    }

                    $compareKeys = $this->keys($baseDir . $compareLang . '/' . basename($file));

                    foreach ($keys as $key => $trans) {
                        // Report if the target file is missing, or the key is missing in the target language
                        if ($compareKeys === null || ! isset($compareKeys[$key])) {
                            fputcsv(
                                STDOUT,
                                [$compareLang, $key, $language, $trans]
                            );
                        }
                    }
                }
            }
        }

        return self::SUCCESS;
    }

    /**
     * Get all available languages
     *
     * @param string $baseDir
     * @return array
     */
    private function languages(string $baseDir): array
    {
        $subDirectories = scandir($baseDir);

        return array_filter($subDirectories, function ($language) use ($baseDir) {
            return is_dir($baseDir . $language)
                && ! in_array($language, ['.', '..']);
        });
    }

    /**
     * Fetch dot notation translation values from a specific file.
     *
     * @param string $file
     * @return array|null Dot notation translation values, or null if the target file is missing
     */
    private function keys(string $file): ?array
    {
        if (! file_exists($file)) {
            return null;
        }

        $data = [pathinfo($file)['filename'] => include $file];

        return array_dot($data);
    }

    /**
     * Get a list of translation files available for a given language
     *
     * @param string $dir
     * @param string $language
     * @return array
     */
    private function translationFiles(string $dir, string $language): array
    {
        return glob($dir . $language . '/*.php');
    }

    /**
     * Get the base directory in which to look for translation subdirectories
     *
     * @return string
     */
    private function baseDir(): string
    {
        $dir = $this->ask(
            'Absolute path to translation base directory',
            app()->langPath()
        );

        return rtrim($dir, '/') . '/';
    }
}
