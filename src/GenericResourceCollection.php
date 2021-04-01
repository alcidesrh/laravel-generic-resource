<?php

namespace Alcidesrh\Generic;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GenericResourceCollection extends ResourceCollection
{
    private $fields;

    public function __construct($query, array $fields = null)
    {
        $this->fields = $fields;
        parent::__construct($query);
    }
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function (GenericResource $resource) use ($request) {
            return $resource->setFields($this->fields)->toArray($request);
        })->all();
    }
}
