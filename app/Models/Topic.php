<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];


    public function scopeParents($query) {
        return $query->whereNotNull('parent_id')->where('parent_id', '!=', 0)->where('parent_id', '!=', '');
    }

    public function scopeOrphans($query) {
        return $query->whereNull('parent_id')->orWhere('parent_id', 0)->orWhere('parent_id', '');
    }

    public function scopeActive($query) {
        return $query->where('status', 1);
    }

    public function content()
    {
        return $this->hasMany(Content::class);
    }

    public function parent() {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function children() {
        return $this->hasMany(self::class, 'parent_id')->with('children');
    }

    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

    public static function getCategoryHierarchy($categoryId)
    {
        return self::where('id', $categoryId)
            ->with(['ancestors', 'descendants'])
            ->first();
    }

    public function getAllDescendants()
    {
        $descendants = collect();
        
        foreach ($this->descendants as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    public function getAllAncestors()
    {
        $ancestors = collect();
        
        if ($this->parent) {
            $ancestors->push($this->parent);
            $ancestors = $ancestors->merge($this->parent->getAllAncestors());
        }
        
        return $ancestors;
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public static function getHierarchyAsTree($categoryId, $onlyIds = false)
    {
        $category = self::getCategoryHierarchy($categoryId);

        if ($onlyIds) {
            return [
                'current' => $category->id,
                'ancestors' => $category->getAllAncestors()->map(function($item) {
                    return $item->id;
                })->values(),
                'descendants' => $category->getAllDescendants()->map(function($item) {
                    return $item->id;
                })->values()
            ];
        } else {
            return [
                'current' => [
                    'id' => $category->id,
                    'name' => $category->name
                ],
                'ancestors' => $category->getAllAncestors()->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name
                    ];
                })->values(),
                'descendants' => $category->getAllDescendants()->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name
                    ];
                })->values()
            ];
        }
    }

    public static function buildTreeFromIds(array $categoryIds, $proData = [])
    {
        $categories = self::whereIn('id', $categoryIds)
            ->with('parent')
            ->get();

        $parentIds = $categories->pluck('parent_id')
            ->filter()
            ->diff($categoryIds)
            ->unique();

        if ($parentIds->isNotEmpty()) {
            $parents = self::whereIn('id', $parentIds)->get();
            $categories = $categories->merge($parents);
        }

        return self::buildTree($categories, $proData);
    }

    private static function buildTree(\Illuminate\Support\Collection $categories, $proData = [], $parentId = null)
    {
        $tree = [];

        $nodes = $categories->where('parent_id', $parentId);

        foreach ($nodes as $node) {
            $children = self::buildTree($categories, $proData, $node->id);
            
            $tree[] = [
                'id' => $node->id,
                'name' => $node->name,
                'children' => $children,
                'parent_id' => $node->parent_id,
                'has_children' => count($children) > 0,
                'level' => self::calculateLevel($categories, $node),
                'content' => collect($proData)->where('topic_id', $node->id)->values()->toArray()
            ];
        }

        return $tree;
    }

    private static function calculateLevel(\Illuminate\Support\Collection $categories, Topic $node)
    {
        $level = 0;
        $current = $node;

        while ($current->parent_id !== null && $current->parent_id !== 0) {
            $level++;
            $current = $categories->firstWhere('id', $current->parent_id);
            if (!$current) break;
        }

        return $level;
    }

    public static function getDescendantsTree($categoryId, $getCurrent = false)
    {
        $category = self::where('id', $categoryId)
            ->with('descendants')
            ->first();

        if (!$category) {
            return [];
        }

        if ($getCurrent) {
            $children = self::formatDescendants($category->descendants);

            return [
                'id' => $category->id,
                'name' => $category->name,
                'has_children' => !empty($children),
                'children' => $children
            ];
            
        } else {
            return self::formatDescendants($category->descendants);
        }
    }
    
    private static function formatDescendants($descendants)
    {
        $result = [];

        foreach ($descendants as $descendant) {
            $result[] = [
                'id' => $descendant->id,
                'name' => $descendant->name,
                'has_children' => $descendant->descendants->isNotEmpty(),
                'children' => self::formatDescendants($descendant->descendants)
            ];
        }

        return $result;
    }
}
