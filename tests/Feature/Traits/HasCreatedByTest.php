<?php

use App\Filters\Filter;
use App\Interfaces\HasCreatedByInterface;
use App\Models\User;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CreatedByNote extends Model implements HasCreatedByInterface
{
    use HasCreatedBy, HasUlids, IsFilterable;

    protected $table = 'created_by_notes';

    protected $guarded = [];
}

beforeEach(function () {
    // Temporary, so the table does not commit the transaction of the test
    Schema::create('created_by_notes', function ($table) {
        $table->temporary();
        $table->ulid('id')->primary();
        $table->string('title');
        $table->foreignUlid('created_by')->nullable();
        $table->timestamps();
    });

    $this->user = $this->signIn();
});

it('sets the creator from the logged user', function () {
    $note = CreatedByNote::create(['title' => 'First']);

    expect($note->created_by)->toBe($this->user->id)
        ->and($note->fresh()->createdBy->is($this->user))->toBeTrue();
});

it('keeps the creator it is given', function () {
    $other = User::factory()->create();

    $note = CreatedByNote::create(['title' => 'First', 'created_by' => $other->id]);

    expect($note->fresh()->createdBy->is($other))->toBeTrue();
});

it('filters by the creators', function () {
    $other = User::factory()->create();
    CreatedByNote::create(['title' => 'Mine']);
    CreatedByNote::create(['title' => 'Theirs', 'created_by' => $other->id]);

    expect(CreatedByNote::filter(['createdBy' => [$other->id]], Filter::class)->pluck('title')->all())->toBe(['Theirs'])
        ->and(CreatedByNote::filter(['createdBy' => [$this->user->id, $other->id]], Filter::class)->get())->toHaveCount(2);
});
