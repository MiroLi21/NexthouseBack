<?php

namespace App\Traits;

trait HasNavigation
{
    /**
     * Get the first record
     */
    public function first()
    {
        $record = $this->modelClass::orderBy('id', 'asc')->first();
        if (!$record) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->SuccessfullyResponse(
            new $this->resourceClass($record->load($this->relations)),
            __('general.loadSuccess')
        );
    }

    /**
     * Get the last record
     */
    public function last()
    {
        $record = $this->modelClass::orderBy('id', 'desc')->first();
        if (!$record) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->SuccessfullyResponse(
            new $this->resourceClass($record->load($this->relations)),
            __('general.loadSuccess')
        );
    }

    /**
     * Get the next record after the given ID
     */
    public function next($id)
    {
        $record = $this->modelClass::where('id', '>', $id)->orderBy('id', 'asc')->first();
        if (!$record) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->SuccessfullyResponse(
            new $this->resourceClass($record->load($this->relations)),
            __('general.loadSuccess')
        );
    }

    /**
     * Get the previous record before the given ID
     */
    public function previous($id)
    {
        $record = $this->modelClass::where('id', '<', $id)->orderBy('id', 'desc')->first();
        if (!$record) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->SuccessfullyResponse(
            new $this->resourceClass($record->load($this->relations)),
            __('general.loadSuccess')
        );
    }
}
