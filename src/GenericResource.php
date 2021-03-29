<?php

namespace Alcidesrh\Generic;

use Illuminate\Http\Resources\Json\JsonResource;

class GenericResource extends JsonResource
{
    private $fields;

    public function __construct($query, $fields = false)
    {
        $this->fields = $fields;
        parent::__construct($query);
    }

    public function setFields($fields)
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
            return \get_object_vars($this)['resource'];
        }

        if (is_array($this->fields)) {

            $return = [];

            foreach ($this->fields as $key => $value) {

                if (\is_array($value)) {

                    foreach ($value as $key2 => $value2) {
                        try {
                            if (\gettype($this->{$key2}) === 'string') {
                                $return[$key2] = $this->{$value2};
                            } else {
                                $return[$key2] = new GenericResource($this->{$key2}, $value2);
                            }
                        } catch (\Throwable $th) {
                           
                        }
                    }
                } else if (\gettype($value) === 'string') {

                    try {
                        $data = $this->{$value};

                        if (\is_numeric($key)) {
                            $return[$value] = $data;
                        } else {
                            $return[$key] = $data;
                        }
                    } catch (\Throwable $th) {
                       
                    }                                       

                }
            }
        }

        return $return ?? null;
    }
}
