<?php

namespace App\Traits;

use App\Interfaces\HasFilesInterface;
use App\Models\File;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;

/**
 * Gives a model files, grouped in the collections it declares in fileCollections().
 * The work is done by the FileService: these methods are just a way to reach it from the model.
 *
 * To read a collection, declare a relation named as it:
 *
 *     public function avatar(): MorphOne
 *     {
 *         return $this->fileOf('avatar');
 *     }
 *
 * @mixin Model
 */
trait HasFiles
{
    /**
     * The files are deleted with the model. A soft deleted model can be restored, so it keeps them.
     */
    public static function bootHasFiles(): void
    {
        $event = in_array(SoftDeletes::class, class_uses_recursive(static::class)) ? 'forceDeleted' : 'deleted';

        static::registerModelEvent($event, fn (HasFilesInterface $model) => $model->deleteFiles());
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function filesOf(string $collection): MorphMany
    {
        return $this->files()->where('collection', $collection);
    }

    public function fileOf(string $collection): MorphOne
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('collection', $collection)
            ->latest('id');
    }

    /**
     * @return array<string, array{single?: bool, mimes?: list<string>, max?: int, visibility?: string}>
     */
    public function fileCollections(): array
    {
        return [];
    }

    /**
     * Saves the uploaded file in the collection. If it is a single file collection, the previous one is replaced.
     */
    public function saveFile(UploadedFile $uploadedFile, string $collection): File
    {
        return app(FileService::class)->save($this, $uploadedFile, $collection);
    }

    /**
     * What a form sends for a file: the uploaded one replaces the current one, and without it, remove deletes it.
     */
    public function syncFile(string $collection, ?UploadedFile $uploadedFile, bool $remove = false): void
    {
        if ($uploadedFile) {
            $this->saveFile($uploadedFile, $collection);
        } elseif ($remove) {
            $this->deleteFile($collection);
        }
    }

    public function deleteFile(string $collection): void
    {
        app(FileService::class)->delete($this, $collection);
    }

    public function deleteFiles(): void
    {
        app(FileService::class)->deleteAll($this);
    }

    /**
     * Validation rules of an uploaded file of the collection, to use in the form requests.
     *
     * @return list<string>
     */
    public static function fileRules(string $collection): array
    {
        $config = app(FileService::class)->collection(new static, $collection);

        return array_values(array_filter([
            'nullable',
            'file',
            isset($config['mimes']) ? 'mimes:'.implode(',', $config['mimes']) : null,
            isset($config['max']) ? 'max:'.$config['max'] : null,
        ]));
    }
}
