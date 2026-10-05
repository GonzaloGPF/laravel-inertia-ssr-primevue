<?php

namespace App\Http\Controllers;

use App\Enums\MessageTypes;
use Inertia\Inertia;
use Inertia\Response;

abstract class Controller
{
    protected function flashMessage(string $message, MessageTypes $level = MessageTypes::SUCCESS): void
    {
        if (request('quietly')) {
            return;
        }
        session()->flash('flash_message_data', [
            'message' => $message,
            'type' => $level->value,
        ]);
    }

    /**
     * Renders a page whose `data` prop (the filtered and paginated models) is only
     * resolved when requested through an Inertia partial reload, as async tables do.
     */
    protected function renderCollection(string $pageComponent, string $modelClass, array $extraFilters = []): Response
    {
        $model = resolve($modelClass);
        $filters = request()
            ->merge($extraFilters)
            ->all();

        return Inertia::render($pageComponent, [
            'data' => Inertia::optional(fn () => $model::filter($filters)->getOrPaginate()),
        ]);
    }
}
