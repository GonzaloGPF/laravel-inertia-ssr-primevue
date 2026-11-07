<?php

namespace App\Models;

use App\Interfaces\HasCreatedByInterface;
use App\Traits\HasCreatedBy;
use Database\Factories\FileFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * A physical file, kept in a disk, that belongs to any model using the HasFiles trait.
 *
 * Only its path is stored: the url is calculated from the disk it was saved in.
 *
 * @property-read string $url
 *
 * @mixin IdeHelperFile
 */
class File extends Model implements HasCreatedByInterface
{
    /** @use HasFactory<FileFactory> */
    use HasCreatedBy, HasFactory, HasUlids;

    protected $fillable = [
        'collection',
        'disk',
        'path',
        'name',
        'mime',
        'size',
    ];

    protected $hidden = [
        'disk',
        'path',
    ];

    protected $appends = [
        'url',
    ];

    protected static function booted(): void
    {
        static::deleted(fn (File $file) => Storage::disk($file->disk)->delete($file->path));
    }

    public function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn () => Storage::disk($this->disk)->url($this->path));
    }
}
