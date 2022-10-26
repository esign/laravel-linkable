<?php

namespace Esign\Linkable\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class BelongsToLinkable extends BelongsTo
{
    protected array $queries = [];

    protected function mapModels(array $models): array
    {
        $map = [];

        foreach ($models as $model) {
            if (empty($model->{$this->foreignKey})) {
                continue;
            }

            [$modelAlias, $modelIdentifier] = explode(':', $model->{$this->foreignKey});
            if (empty($modelAlias) || empty($modelIdentifier)) {
                continue;
            }

            if (! $class = Relation::getMorphedModel($modelAlias)) {
                continue;
            }

            $map[$class][] = $modelIdentifier;
        }

        $map = array_map(function ($ids) {
            return array_unique($ids);
        }, $map);

        return $map;
    }

    public function addConstraints(): void
    {
        if ($this->parent->exists) {
            $this->queries([$this->parent]);
        }
    }

    public function addEagerConstraints(array $models): void
    {
        $this->queries($models);
    }

    protected function queries(array $models): void
    {
        $entities = $this->mapModels($models);

        foreach ($entities as $className => $ids) {
            $this->queries[$className] = (new $className())->newQuery()->whereIn($this->ownerKey, $ids);
        }
    }

    public function getResults()
    {
        $queries = $this->queries;
        $query = array_shift($queries);

        return $query ? $query->first() : $this->getDefaultFor($this->parent);
    }

    public function getEager()
    {
        $results = new Collection();
        foreach ($this->queries as $className => $query) {
            $entities = $query->get();
            foreach ($entities as $entity) {
                $key = implode(':', [array_search($className, Relation::morphMap()), $entity->{$this->ownerKey}]);
                $results->put($key, $entity);
            }
        }

        return $results;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation): array
    {
        foreach ($models as $model) {
            if (empty($model->{$this->foreignKey})) {
                continue;
            }

            $match = $results->get($model->{$this->foreignKey});
            $model->setRelation($relation, $match);
        }

        return $models;
    }
}
