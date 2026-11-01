<?php

namespace App\Services;

use App\Interfaces\HasFilesInterface;
use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class FileService
{
    /**
     * Saves the uploaded file in a collection of the model, in the files disk.
     * In a single file collection, the previous file is replaced: it is deleted once the new one is saved,
     * so a failure never leaves the model without its file.
     */
    public function save(Model&HasFilesInterface $model, UploadedFile $uploadedFile, string $collection): File
    {
        $config = $this->collection($model, $collection);
        $disk = $this->disk();

        $path = Storage::disk($disk)->putFile(
            $this->directory($model, $collection),
            $uploadedFile,
            $config['visibility'] ?? 'public',
        );

        if ($path === false) {
            throw new RuntimeException("The file could not be saved in the [$disk] disk.");
        }

        try {
            $previous = ($config['single'] ?? false) ? $model->filesOf($collection)->get() : collect();

            $file = $model->files()->create([
                'collection' => $collection,
                'disk' => $disk,
                'path' => $path,
                'name' => Str::limit($uploadedFile->getClientOriginalName(), 255, ''),
                'mime' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
            ]);
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }

        $previous->each->delete();

        $this->forget($model, $collection);

        return $file;
    }

    /**
     * Deletes the files of a collection of the model, with their physical files.
     */
    public function delete(Model&HasFilesInterface $model, string $collection): void
    {
        $this->collection($model, $collection);

        $model->filesOf($collection)->get()->each->delete();

        $this->forget($model, $collection);
    }

    /**
     * Deletes every file of the model, with their physical files.
     */
    public function deleteAll(Model&HasFilesInterface $model): void
    {
        $model->files()->get()->each->delete();

        $model->unsetRelation('files');
    }

    /**
     * The disk where the new files are saved.
     */
    public function disk(): string
    {
        return config('filesystems.files_disk');
    }

    /**
     * The configuration of a collection of the model.
     *
     * @return array{single?: bool, mimes?: list<string>, max?: int, visibility?: string}
     *
     * @throws InvalidArgumentException If the model does not declare the collection.
     */
    public function collection(HasFilesInterface $model, string $collection): array
    {
        $collections = $model->fileCollections();

        if (! array_key_exists($collection, $collections)) {
            throw new InvalidArgumentException(sprintf(
                'The [%s] model has no [%s] file collection.',
                get_class($model),
                $collection,
            ));
        }

        return $collections[$collection];
    }

    /**
     * Where the files of a collection are saved: users/{ulid}/avatar (the model is named by its morph alias).
     */
    public function directory(Model $model, string $collection): string
    {
        return implode('/', [
            $this->folder($model->getMorphClass()),
            $model->getKey(),
            $collection,
        ]);
    }

    /**
     * Deletes the folder of every model from the files disk, with whatever is in them, whether the rows
     * of those files exist or not (i.e. after dropping the database).
     */
    public function deleteAllFolders(): void
    {
        collect(Relation::morphMap())->keys()->each(
            fn (string $alias) => Storage::disk($this->disk())->deleteDirectory($this->folder($alias))
        );
    }

    /**
     * The folder where the files of a model are saved, named after its morph alias.
     */
    private function folder(string $morphAlias): string
    {
        return Str::plural($morphAlias);
    }

    /**
     * The loaded files of the model are not the current ones anymore: they are read again when needed.
     */
    private function forget(Model $model, string $collection): void
    {
        $model->unsetRelation('files');
        $model->unsetRelation($collection);
    }
}
