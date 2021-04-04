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

        if (!$this->fields) {

            $data = \get_object_vars($this)['resource'];
            $data = $data->toArray();

            if (isset($data['pivot'])) {
                unset($data['pivot']);
            }

            return $data;

        } else if (is_array($this->fields)) {

            $data = [];

            foreach ($this->fields as $key => $value) {

                if (\is_array($value)) {

                    foreach ($value as $key2 => $value2) {
                        try {
                            if ($this->{$key2} instanceof Collection) {
                                $data[$key2] = new GenericResourceCollection($this->{$key2}, $value2);
                            } else if (\gettype($this->{$key2}) === 'object') {
                                $data[$key2] = new GenericResource($this->{$key2}, $value2);
                            }
                            //Property name change
                            else if(\gettype($this->{$value2}) === 'string' && ($this->$value2 || method_exists($this->resource, $value2) || property_exists($this->resource, $value2)))
                                $data[$key2] = $this->$value2;
                            else if (method_exists($this->resource, $key2) || property_exists($this->resource, $key2)) {
                                $data[$key2] = $this->$key2;
                            }

                        } catch (\Throwable $th) {

                        }
                    }
                } else {

                    try {

                        $newKey = !\is_numeric($key) ? $key : $value;

                        if ($this->{$value} instanceof Collection) {
                            $data[$newKey] = new GenericResourceCollection($this->{$value});
                        } else if (\gettype($this->{$value}) === 'object') {
                            $data[$newKey] = new GenericResource($this->{$value});
                        } else if ($this->{$value} || method_exists($this->resource, $value) || property_exists($this->resource, $value)) {
                            $data[$newKey] = $this->{$value};
                        }

                    } catch (\Throwable $th) {

                    }

                }
            }
        }

        return $data ?? null;
    }
}
