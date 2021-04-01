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
                            } else if (\gettype($this->{$key2}) === 'string') {
                                $data[$key2] = $this->{$value2};
                            }

                        } catch (\Throwable $th) {

                        }
                    }
                } else if (\gettype($value) === 'string') {

                    try {

                        $newKey = !\is_numeric($key) ? $key : $value;

                        if ($this->{$value} instanceof Collection) {
                            $data[$newKey] = new GenericResourceCollection($this->{$value});
                        } else if (\gettype($this->{$value}) === 'object') {
                            $data[$newKey] = new GenericResource($this->{$value});
                        } else if (\gettype($this->{$value}) === 'string') {
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
