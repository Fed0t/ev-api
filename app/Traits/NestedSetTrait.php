<?php

namespace App\Traits;

use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

trait NestedSetTrait
{
    protected string $leftColumn = 'left';

    protected string $parentColumn = 'parent_id';

    protected string $rightColumn = 'right';

    /**
     *  List descendants hierarchically
     *
     * @return mixed
     */
    public function descendants(): HasMany
    {
        return $this->hasMany(self::class, $this->parentColumn)
            ->orWhere(function ($query) {
                $query->where($this->leftColumn, '>', (int) $this->{$this->leftColumn})
                    ->where($this->leftColumn, '<', (int) $this->{$this->rightColumn});
            })
            ->orderBy($this->leftColumn);
    }

    /**
     *  Add new descendant to parent
     *
     * @throws Exception
     */
    public function addDescendant($child): void
    {
        $this->newQuery()->where($this->leftColumn, '>=', $this->{$this->rightColumn})
            ->increment($this->leftColumn, 2);

        $this->newQuery()->where($this->rightColumn, '>=', $this->{$this->rightColumn})
            ->increment($this->rightColumn, 2);

        if (! $child->parent_id) {

            $child->{$this->leftColumn} = $this->{$this->rightColumn};
            $child->{$this->rightColumn} = $child->left + 1;
            $child->{$this->parentColumn} = $this->id;

            $child->save();
        }

    }

    /**
     *  Delete descendant
     */
    public function deleteDescendant(): void
    {
        $left = $this->{$this->leftColumn};
        $right = $this->{$this->rightColumn};
        $parentId = $this->{$this->parentColumn};

        // Delete node and its descendants
        $this->delete();

        // Update left and right values of parent's descendants
        $this->where('parent_id', $parentId)
            ->where('left', '>', $left)
            ->where('right', '<', $right)
            ->update([
                'left' => DB::raw('CASE WHEN "left" > '.$left.' THEN "left" - 2 ELSE "left" END'),
                'right' => DB::raw('CASE WHEN "right" > '.$left.' THEN "right" - 2 ELSE "right" END'),
            ]);

        // Update left and right values of parent's right-side nodes
        $this->newQuery()
            ->where('parent_id', $parentId)
            ->where('left', '>', $right)
            ->update([
                'left' => DB::raw('"left" - 2'),
                'right' => DB::raw('"right" - 2'),
            ]);
    }

    /**
     * Get the latest left and right values
     */
    public static function getLeftRight(): array
    {
        $lastNode = self::orderBy('right', 'desc')->first();

        $leftValue = ($lastNode) ? $lastNode->right + 1 : 1;
        $rightValue = $leftValue + 1;

        return ['left' => $leftValue, 'right' => $rightValue];
    }
}
