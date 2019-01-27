<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;

/**
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|NestedEntity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NestedEntity newQuery()
 * @method static \Illuminate\Database\Query\Builder|NestedEntity onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|NestedEntity query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|NestedEntity withTrashed()
 * @method static \Illuminate\Database\Query\Builder|NestedEntity withoutTrashed()
 */
class NestedEntity extends Model
{
    use SoftDeletes;

    protected $table = "nested_entities";

    protected $guarded = ["left_range", "right_range"];

    const SELECT_ALL_WITH_MINIMUM_INFO = 1;

    const SELECT_SINGLE_PATH_ONLY = 2;

    const SELECT_WITH_DEPTH_INFO = 4;

    const SELECT_LEAVES_ONLY = 8;

    /**
     * @param string $newEntityName
     * @param int    $referenceEntityId
     *
     * @return boolean
     * @throws \Throwable
     */
    public function insertIntoAtTheBeginning(string $newEntityName, int $referenceEntityId): bool
    {
        # Fetch reference entity
        $referenceEntity = app('db.connection')->table($this->table)->where('id', $referenceEntityId)->first();
        if (is_null($referenceEntity)) {
            throw new \InvalidArgumentException("Reference entity with id: " . $referenceEntityId . " not found!");
        }

        return app('db.connection')->transaction(
            function () use ($newEntityName, $referenceEntity) {
                # Create new entity
                $newEntity = app('db.connection')->table($this->table);

                # Update ranges in preparation of insertion
                app('db.connection')->table($this->table)
                    ->where('right_range', '>', $referenceEntity->left_range)
                    ->update(['right_range' => app('db.connection')->raw('right_range + 2')]);
                app('db.connection')->table($this->table)
                    ->where('left_range', '>', $referenceEntity->left_range)
                    ->update(['left_range' => app('db.connection')->raw('left_range + 2')]);

                # Insert now
                return $newEntity->insert([
                    'name' => $newEntityName,
                    'left_range' => $referenceEntity->left_range + 1,
                    'right_range' => $referenceEntity->left_range + 2,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        );
    }

    /**
     * @param string $newEntityName
     * @param int    $referenceEntityId
     *
     * @return boolean
     * @throws \Throwable
     */
    public function insertIntoAtTheEnd(string $newEntityName, int $referenceEntityId): bool
    {
        # Fetch reference entity
        $referenceEntity = app('db.connection')->table($this->table)->where('id', $referenceEntityId)->first();
        if (is_null($referenceEntity)) {
            throw new \InvalidArgumentException("Reference entity with id: " . $referenceEntityId . " not found!");
        }

        return app('db.connection')->transaction(
            function () use ($newEntityName, $referenceEntity) {
                # Create new entity
                $newEntity = app('db.connection')->table($this->table);

                # Update ranges in preparation of insertion
                app('db.connection')->table($this->table)
                    ->where('right_range', '>=', $referenceEntity->right_range)
                    ->update(['right_range' => app('db.connection')->raw('right_range + 2')]);
                app('db.connection')->table($this->table)
                    ->where('left_range', '>', $referenceEntity->right_range)
                    ->update(['left_range' => app('db.connection')->raw('left_range + 2')]);

                # Insert now
                return $newEntity->insert([
                    'name' => $newEntityName,
                    'left_range' => $referenceEntity->right_range,
                    'right_range' => $referenceEntity->right_range + 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        );
    }

    /**
     * Alias to insertIntoAtTheEnd()
     *
     * @param        $newEntityName
     * @param int    $referenceEntityId
     *
     * @return boolean
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function insertInto(string $newEntityName, int $referenceEntityId): bool
    {
        return $this->insertIntoAtTheEnd($newEntityName, $referenceEntityId);
    }

    /**
     * @param string $newEntityName
     * @param int    $referenceEntityId
     *
     * @return boolean
     * @throws \Throwable
     */
    public function prependTo(string $newEntityName, int $referenceEntityId): bool
    {
        # Fetch reference entity
        $referenceEntity = app('db.connection')->table($this->table)->where('id', $referenceEntityId)->first();
        if (is_null($referenceEntity)) {
            throw new \InvalidArgumentException("Reference entity with id: " . $referenceEntityId . " not found!");
        }

        return app('db.connection')->transaction(
            function () use ($newEntityName, $referenceEntity) {
                # Create new entity
                $newEntity = app('db.connection')->table($this->table);

                # Update ranges in preparation of insertion
                app('db.connection')->table($this->table)
                    ->where('right_range', '>', $referenceEntity->left_range)
                    ->update(['right_range' => app('db.connection')->raw('right_range + 2')]);
                app('db.connection')->table($this->table)
                    ->where('left_range', '>=', $referenceEntity->left_range)
                    ->update(['left_range' => app('db.connection')->raw('left_range + 2')]);

                # Insert now
                return $newEntity->insert([
                    'name' => $newEntityName,
                    'left_range' => $referenceEntity->left_range,
                    'right_range' => $referenceEntity->right_range,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        );
    }

    /**
     * @param string $newEntityName
     * @param int    $referenceEntityId
     *
     * @return boolean
     * @throws \Throwable
     */
    public function appendTo(string $newEntityName, int $referenceEntityId): bool
    {
        # Fetch reference entity
        $referenceEntity = app('db.connection')->table($this->table)->where('id', $referenceEntityId)->first();
        if (is_null($referenceEntity)) {
            throw new \InvalidArgumentException("Reference entity with id: " . $referenceEntityId . " not found!");
        }

        return app('db.connection')->transaction(
            function () use ($newEntityName, $referenceEntity) {
                # Create new entity
                $newEntity = app('db.connection')->table($this->table);

                # Update ranges in preparation of insertion
                app('db.connection')->table($this->table)
                    ->where('right_range', '>', $referenceEntity->right_range)
                    ->update(['right_range' => app('db.connection')->raw('right_range + 2')]);
                app('db.connection')->table($this->table)
                    ->where('left_range', '>', $referenceEntity->right_range)
                    ->update(['left_range' => app('db.connection')->raw('left_range + 2')]);

                # Insert now
                return $newEntity->insert([
                    'name' => $newEntityName,
                    'left_range' => $referenceEntity->right_range + 1,
                    'right_range' => $referenceEntity->right_range + 2,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        );
    }

    /**
     * @param int  $id
     * @param bool $doSoftDelete
     *
     * @return mixed
     * @throws \Throwable
     */
    public function remove(int $id, bool $doSoftDelete = true)
    {
        # Round up delete-ables
        $referenceEntity = app('db.connection')->table($this->table)->select('left_range', 'right_range', app('db.connection')->raw('right_range - left_range + 1 as range_width'))->where('id', $id)
            ->first();
        if (is_null($referenceEntity)) {
            throw new \InvalidArgumentException("Reference entity with id: " . $id . " not found!");
        }
        $completeListOfEntitiesToDeleteIncludingOrphans = app('db.connection')->table($this->table)
            ->where('left_range', '>=', $referenceEntity->left_range)
            ->where('left_range', '<=', $referenceEntity->right_range);

        # Perform either a soft-delete or hard-delete
        return app('db.connection')->transaction(
            function () use ($referenceEntity, $doSoftDelete, $completeListOfEntitiesToDeleteIncludingOrphans) {
                if ($doSoftDelete) {
                    # Soft delete
                    $removeResult = $completeListOfEntitiesToDeleteIncludingOrphans->update(['deleted_at' => Carbon::now()]);
                } else {
                    # Hard delete
                    $removeResult = $completeListOfEntitiesToDeleteIncludingOrphans->delete();

                    # Update ranges
                    app('db.connection')->table($this->table)
                        ->where('right_range', '>', $referenceEntity->right_range)
                        ->update(['right_range' => app('db.connection')->raw('right_range - ' . $referenceEntity->range_width)]);
                    app('db.connection')->table($this->table)
                        ->where('left_range', '>', $referenceEntity->right_range)
                        ->update(['left_range' => app('db.connection')->raw('left_range - ' . $referenceEntity->range_width)]);
                }

                return $removeResult;
            }
        );
    }

    /**
     * @param  int    $flag Parameters of Select, which are defined bitwise (see self:SELECT__* constants)
     * @param  string $id   Path information: used only if anything path related is requested.
     *
     * @return array|static[]
     * @throws \InvalidArgumentException
     */
    public function fetch($flag = self::SELECT_ALL_WITH_MINIMUM_INFO, $id = null)
    {
        # Error scenarios
        if ($flag & self::SELECT_ALL_WITH_MINIMUM_INFO && ($flag & self::SELECT_WITH_DEPTH_INFO || $flag & self::SELECT_SINGLE_PATH_ONLY)) {
            throw new \InvalidArgumentException("SELECT_ALL_WITH_MINIMUM_INFO bit isn't compatible with other bits. Use it alone!");
        } elseif ($flag & self::SELECT_SINGLE_PATH_ONLY && empty($id)) {
            throw new \InvalidArgumentException("SELECT_SINGLE_PATH_ONLY requires leaf category ID!");
        } elseif ($flag & self::SELECT_SINGLE_PATH_ONLY && $flag & self::SELECT_WITH_DEPTH_INFO) {
            throw new \InvalidArgumentException("SELECT_SINGLE_PATH_ONLY bit isn't compatible with SELECT_WITH_DEPTH_INFO - their results are mutually restrictive from opposing ends!");
        }

        # Prelim
        empty($id) && $id = 1;
        $nestedEntities = app('db.connection')->table($this->table . ' as node')
            ->select('node.id', 'node.name')
            ->leftJoin(
                $this->table . ' AS parent',
                function (JoinClause $join) {
                    $join->on('node.left_range', '<=', 'parent.right_range')
                        ->on('node.left_range', '>=', 'parent.left_range');
                });

        # Scenario-1: Select'ing *single path only* with leaf node at the end of that path
        $flag == self::SELECT_SINGLE_PATH_ONLY && $nestedEntities->select('parent.id', 'parent.name')->where('node.id', '=', $id)->orderBy('parent.left_range');

        # Scenario-2: Select'ing *descendents* of provided parent-entity, with the bare minumum
        $flag == self::SELECT_ALL_WITH_MINIMUM_INFO && $nestedEntities->where('parent.id', '=', $id)->orderBy('node.left_range');

        # Scenario-3: Select'ing *everything* with depth information
        $flag == self::SELECT_WITH_DEPTH_INFO && $nestedEntities->addSelect(app('db.connection')
            ->raw('(COUNT(parent.name)-1) as depth'))
            ->groupBy('node.id', 'node.name')
            ->orderBy('node.left_range');

        # Scenario-4: Fetches leaves only
        $flag == self::SELECT_LEAVES_ONLY && $nestedEntities = app('db.connection')->table($this->table)
            ->select('id', 'name')
            ->where('right_range', '=', app('db.connection')
            ->raw('left_range + 1'))
            ->orderBy('left_range');
        if ($flag == self::SELECT_LEAVES_ONLY && $id !== 1) {
            $parentEntity = app('db.connection')->table($this->table)->select('left_range', 'right_range')->where('id', $id)->first();
            $nestedEntities->whereBetween('left_range', [$parentEntity->left_range, $parentEntity->right_range]);
        }

        return $nestedEntities->get();
    }
}
