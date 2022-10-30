<?php

namespace Esign\Linkable\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SingleColumnMorphTo extends MorphTo
{
    public function __construct(Builder $query, Model $parent, $foreignKey, $ownerKey, $relation)
    {
        parent::__construct($query, $parent, $foreignKey, $ownerKey, '', $relation);
    }

    public static function getSingleColumnMorphingTypeAndKey(Model $model, string $foreignKey): array
    {
        return explode(':', $model->{$foreignKey});
    }

    public static function getSingleColumnMorphingType(Model $model, string $foreignKey): ?string
    {
        return static::getSingleColumnMorphingTypeAndKey($model, $foreignKey)[0] ?? null;
    }

    public static function getSingleColumnMorphingKey(Model $model, string $foreignKey): ?string
    {
        return static::getSingleColumnMorphingTypeAndKey($model, $foreignKey)[1] ?? null;
    }

    public static function hasSingleColumnMorphingTypeAndKey(Model $model, string $foreignKey): bool
    {
        return count(self::getSingleColumnMorphingTypeAndKey($model, $foreignKey)) === 2;
    }

    /**
     * This method is overwritten from the MorphTo base class.
     * Because regular morphing uses two columns we first have to
     * split the single column up into two pieces.
     * 
     * @see \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    protected function buildDictionary(Collection $models): void
    {
        foreach ($models as $model) {
            if ($this->hasSingleColumnMorphingTypeAndKey($model, $this->foreignKey)) {
                [$relatedType, $relatedId] = $this->getSingleColumnMorphingTypeAndKey($model, $this->foreignKey);
                $morphTypeKey = $this->getDictionaryKey($relatedType);
                $foreignKeyKey = $this->getDictionaryKey($relatedId);

                $this->dictionary[$morphTypeKey][$foreignKeyKey][] = $model;
            }
        }
    }

    /**
     * This method is extended from the underlying BelongsTo class.
     * Because regular morphing uses two columns we first have to
     * split the single column up into two pieces.
     * 
     * @see \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            // For belongs to relationships, which are essentially the inverse of has one
            // or has many relationships, we need to actually query on the primary key
            // of the related models matching on the foreign key that's on a parent.
            $table = $this->related->getTable();

            $relatedId = static::getSingleColumnMorphingKey($this->child, $this->foreignKey);

            $this->query->where($table.'.'.$this->ownerKey, '=', $relatedId);
        }
    }

    /**
     * This method is overwritten from the MorphTo base class.
     * Regular morphing uses two columns to associate a model.
     * Hence why we implode the morph type and morph id into a single column.
     * 
     * @see \Illuminate\Database\Eloquent\Relations\MorphTo
     * 
     * @param \Illuminate\Database\Eloquent\Model | null $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function associate($model): Model
    {
        if ($model instanceof Model) {
            $foreignKey = $this->ownerKey && $model->{$this->ownerKey}
                ? $this->ownerKey
                : $model->getKeyName();
            $morphClass = $model->getMorphClass();
            $foreignKeyKey = $model->{$foreignKey};
            $ownerKeyKey = implode(':', [$morphClass, $foreignKeyKey]);
        }

        $this->parent->setAttribute(
            $this->foreignKey,
            $model instanceof Model ? $ownerKeyKey : null
        );

        return $this->parent->setRelation($this->relationName, $model);
    }

    /**
     * This method is overwritten from the MorphTo base class.
     * Regular morphing uses two columns to associate a model.
     * We should only clear our single morphing column.
     * 
     * @see \Illuminate\Database\Eloquent\Relations\MorphTo
     * 
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function dissociate(): Model
    {
        $this->parent->setAttribute($this->foreignKey, null);

        return $this->parent->setRelation($this->relationName, null);
    }
}
