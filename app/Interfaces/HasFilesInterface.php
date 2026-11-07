<?php

namespace App\Interfaces;

use App\Models\File;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A model that owns files, grouped in named collections.
 *
 * @see HasFiles
 */
interface HasFilesInterface
{
    /**
     * @return MorphMany<File, covariant Model>
     */
    public function files(): MorphMany;

    /**
     * @return MorphMany<File, covariant Model>
     */
    public function filesOf(string $collection): MorphMany;

    /**
     * Deletes all of its files, the physical ones too.
     */
    public function deleteFiles(): void;

    /**
     * The collections of files of the model, by name:
     * - single: only one file is kept, saving another one replaces it.
     * - mimes: the allowed extensions (checked with the file content, not its name).
     * - max: the maximum size, in kilobytes.
     * - visibility: public (default) or private.
     *
     * @return array<string, array{single?: bool, mimes?: list<string>, max?: int, visibility?: string}>
     */
    public function fileCollections(): array;
}
