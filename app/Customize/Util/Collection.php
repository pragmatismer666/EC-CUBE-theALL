<?php

namespace Customize\Util;

use Doctrine\Common\Collections\Collection as CollectionInterface;
use Doctrine\Common\Collections\ArrayCollection;

class Collection extends ArrayCollection implements CollectionInterface
{
    /**
     * @param CollectionInterface|array $collection
     * @return Collection
     */
    public static function from($collection)
    {
        if (is_array($collection)) {
            return new Collection($collection);
        }
        return new Collection($collection->toArray());
    }

    /**
     * @param \Closure $sort
     * @return Collection
     */
    public function sort($sort)
    {
        $array = $this->toArray();
        usort($array, $sort);
        return $this->createFrom($array);
    }

    /**
     * @param \Doctrine\Common\Collections\Collection|array $collection
     * @return Collection
     */
    public function merge($collection)
    {
        $array = $this->toArray();
        if (!is_array($collection)) {
            $collection = $collection->toArray();
        }
        $array = array_merge($array, $collection);
        return $this->createFrom($array);
    }

    /**
     * @param $keyExtract
     * @param \Closure|null $sort
     * @return Collection
     */
    public function unique($keyExtract, $sort = null)
    {
        $keys = array_unique($this->map(function ($item) use ($keyExtract) {
            return $keyExtract($item);
        })->toArray());
        $elements = [];
        foreach($keys as $key) {
            $matching = $this->filter(function ($item) use ($keyExtract, $key) {
                return $keyExtract($item) === $key;
            });
            if (is_callable($sort)) {
                $matching = $matching->sort($sort);
            }
            $element = $matching->first();
            if ($element) {
                $elements[] = $element;
            }
        }
        return $this->createFrom($elements);
    }

    /**
     * @param int $offset
     * @param null $length
     * @return Collection
     */
    public function slice($offset, $length = null)
    {
        $elements = parent::slice($offset, $length);
        return $this->createFrom($elements);
    }
}
