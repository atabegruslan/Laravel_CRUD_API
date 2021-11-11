<?php

namespace App\Models\Traits;

use App\Events\NewEntryMade;
use Illuminate\Support\Facades\DB;
use App\Models\Entry;

trait ChangeAware
{
    /* The 3 functions below are overridden from \Illuminate\Database\Eloquent\Model */

    /**
     * Insert the given attributes and set the ID on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $attributes
     * @return void
     */
    protected function insertAndSetId(\Illuminate\Database\Eloquent\Builder $query, $attributes)
    {
        $keyName              = $this->getKeyName();
        $id                   = $query->insertGetId($attributes, $keyName);
        $attributes[$keyName] = $id;
        $table                = $this->getTable();
        $model                = static::class;

        if ($model === Entry::class)
        {
            event(new NewEntryMade($attributes));
        }

        activity()
            ->performedOn($model::findOrFail($id))
            ->causedBy( auth()->user() )
            ->withProperties(['attributes' => $attributes])
            ->log('A new ' . $model . ' was created.');

        $this->setAttribute($keyName, $id);
    } 

    /**
     * Perform a model update operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performUpdate(\Illuminate\Database\Eloquent\Builder $query)
    {
        $keyName    = $this->getKeyName();
        $attributes = $this->getAttributes();
        $id         = $attributes[$keyName];
        $table      = $this->getTable();
        $model      = static::class;
        $result     = parent::performUpdate($query);

        if ($result)
        {
            activity()
                ->performedOn($model::findOrFail($id))
                ->causedBy( auth()->user() )
                ->withProperties(['attributes' => $attributes])
                ->log($model . ' was updated.');
        }

        return $result;
    }

    /**
     * Delete the model from the database.
     *
     * @return bool|null
     *
     * @throws \LogicException
     */
    public function delete()
    {
        $keyName      = $this->getKeyName();
        $attributes   = $this->getAttributes();
        $id           = $attributes[$keyName];
        $table        = $this->getTable();
        $model        = static::class;

        $this->mergeAttributesFromClassCasts();

        if (is_null($keyName)) 
        {
            throw new LogicException('No primary key defined on model.');
        }

        if (!$this->exists) 
        {
            return;
        }

        if ($this->fireModelEvent('deleting') === false) 
        {
            return false;
        }

        $this->touchOwners();

        activity()
            ->performedOn($model::findOrFail($id))
            ->causedBy( auth()->user() )
            ->withProperties(['attributes' => $attributes])
            ->log($model . ' was deleted.');

        $this->performDeleteOnModel();

        $this->fireModelEvent('deleted', false);

        return true;
    }
}
