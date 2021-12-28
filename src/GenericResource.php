<?php

namespace Alcidesrh\Generic;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class GenericResource extends JsonResource
{
    private $fields;

    public function __construct($query, $fields = null)
    {
        $this->fields = $fields;
        parent::__construct($query);
    }

    public function setFields(array $fields = null)
    {
        $this->fields = $fields;
        return $this;
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if (is_array($this->fields)) {

            $data = [];

            foreach ($this->fields as $key => $value) {

                if (\is_array($value)) {

                        try {
                            if ($this->$key instanceof Collection) {
                                $data[$key] = new GenericResourceCollection($this->$key, $value);
                            } else if (\gettype($this->$key) === 'object') {
                                $data[$key] = new GenericResource($this->$key, $value);
                            }

                        } catch (\Throwable $th) {

                        }
                } else {

                    try {

                        $newKey = !\is_numeric($key) ? $key : $value;

                        if ($this->$value instanceof Collection) {
                            $data[$newKey] = new GenericResourceCollection($this->$value);
                        } else if (\gettype($this->$value) === 'object') {
                            $data[$newKey] = new GenericResource($this->$value);
                        } else if (array_key_exists($value, $this->resource->getAttributes()) || method_exists($this->resource, $value) || property_exists($this->resource, $value)) {
                            $data[$newKey] = $this->$value;
                    }

                    } catch (\Throwable $th) {

                    }

                }
            }
        }
        else if (is_null($this->fields)) {

            $data = \get_object_vars($this)['resource'];
            $data = $data->toArray();

            if (isset($data['pivot'])) {
                unset($data['pivot']);
            }

        }

        return $data ?? null;
    }
}

