<?php

namespace Alcidesrh\Generic;

use Alcidesrh\Generic\GenericResource;
use Alcidesrh\Generic\GenericResourceCollection;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenericController
{    
    function list(Request $request) {

        try {
            $query = DB::table($request->table);

            if (is_array($request->fields)) {
                $query->selectRaw(implode(',', $request->fields));
            }

            $this->checkForWhereClause($request, $query);

            $this->checkForOrderClause($request, $query);

            if ($page = $request->get(config('generic-resource.pagination.name_param_page', 'page'))) {

                if (!$request->has('page')) {
                    $request->request->add(['page' => $page]);
                }

                $perPage = $request->get(config('generic-resource.pagination.name_param_item_per_page', 'itemsPerPage'), config('generic-resource.pagination.itemsPerPage', 20));

                return new GenericResourceCollection($query->paginate($perPage), $request->fields);
            }

            return new GenericResourceCollection($query->get(), $request->fields);

        } catch (\Throwable $th) {

            $message = $th->getMessage();

            if ($th instanceof QueryException) {
                return response($th->errorInfo[2] ?? $message, 500);
            }

            return response($message, 500);
        }

    }

    public function create(Request $request)
    {

        try {
            if ($values = $request->values) {
                $query = DB::table($request->table);
                if ($id = $query->insertGetId($values)) {
                    if (is_array($request->fields)) {
                        $query->selectRaw(implode(',', $request->fields));
                    }
                    $item = $query->where('id', $id)->first();
                }
            }

            if ($values = $request->many) {
                $query = DB::table($request->table);
                $ids = [];
                foreach ($values as $value) {
                    if ($id = $query->insertGetId($value)) {
                        $ids[] = $id;
                    }

                }
                if (is_array($request->fields)) {
                    $query->selectRaw(implode(',', $request->fields));
                }
                return new GenericResourceCollection($query->whereIn('id', $ids)->get(), $request->fields);
            }

            return new GenericResource($item, $request->fields);

        } catch (\Throwable $th) {

            $message = $th->getMessage();

            if ($th instanceof QueryException) {
                return response($th->errorInfo[2] ?? $message, 500);
            }

            return response($message, 500);
        }

    }

    public function update(Request $request)
    {

        try {
            if ($values = $request->values) {
                if ($id = $request->id) {
                    $query = DB::table($request->table);
                    if ($query->where('id', $id)->exists()) {
                        $query->update($values);
                        if (is_array($request->fields)) {
                            $query->selectRaw(implode(',', $request->fields));
                        }
                        $item = $query->where('id', $id)->first();
                    }
                }
                if ($ids = $request->many) {
                    $query = DB::table($request->table);
                    foreach ($ids as $id) {
                        $query2 = clone $query;
                        $query2->where('id', $id)->update($values);
                    }
                    if (is_array($request->fields)) {
                        $query->selectRaw(implode(',', $request->fields));
                    }
                    return new GenericResourceCollection($query->whereIn('id', $ids)->get(), $request->fields);
                }

                return new GenericResource($item, $request->fields);
            }

        } catch (\Throwable $th) {

            $message = $th->getMessage();

            if ($th instanceof QueryException) {
                return response($th->errorInfo[2] ?? $message, 500);
            }

            return response($message, 500);
        }

    }

    public function item(Request $request)
    {

        try {
            if ($id = $request->id) {
                $query = DB::table($request->table);
                if (is_array($request->fields))
                    $query->selectRaw(implode(',', $request->fields));
                if ($item = $query->where('id', $id)->first()) {
                    return GenericResource::make($item, $request->fields);
                }
            }
        } catch (\Throwable $th) {

            $message = $th->getMessage();

            if ($th instanceof QueryException) {
                return response($th->errorInfo[2] ?? $message, 500);
            }

            return response($message, 500);
        }

    }

    public function delete(Request $request)
    {

        try {
            if ($id = $request->id) {
                $query = DB::table($request->table);
                return $query->where('id', $id)->delete();
            }
        } catch (\Throwable $th) {

            $message = $th->getMessage();

            if ($th instanceof QueryException) {
                return response($th->errorInfo[2] ?? $message, 500);
            }

            return response($message, 500);
        }

    }

    public function checkForWhereClause(Request $request, &$query)
    {
        $spreadParam = function (&$query, $field, $param, $clauseType = 'where') {

            if (isset($param['operator']) && !in_array($param['operator'], ['=', '!=', '<', '<=', '>', '>=', '<>', 'like', 'contain'])) {
                throw new Exception("Operator {$param['operator']} is not allowed.");
            }

            if (is_array($param)) {

                if (in_array($clauseType, ['whereBetween', 'whereNotBetween', 'whereIn', 'whereNotIn'])) {
                    $query->{$clauseType}($field, $param);
                } else {
                    $data = $param['value'] ?? $param;
                    if (!is_array($data)) {
                        $data = [$data];
                    }

                    foreach ($data as $value) {
                        if (isset($param['operator']) && $param['operator'] == 'contain') {
                            $searchTerms = array_filter(explode(" ", $value), function ($value) {
                                return !empty($value);
                            });
                            $query
                                ->where(function ($query) use ($searchTerms, $field, $clauseType) {
                                    foreach ($searchTerms as $searchTerm) {
                                        $sql = "LOWER({$field}) LIKE ?";
                                        $searchTerm = mb_strtolower($searchTerm, 'UTF8');
                                        $query->{$clauseType . 'Raw'}($sql, ["%{$searchTerm}%"]);
                                    }
                                });

                        } else {
                            $query->{$clauseType}($field, $param['operator'] ?? '=', $value);
                        }

                    }
                }
            } else if ($param) {
                $query->{$clauseType}($field, $param);
            } else {
                if (isset($param['operator']) && $param['operator'] == '!=') {
                    $query->{$clauseType . 'NotNull'}($field);
                } else {
                    $query->{$clauseType . 'NULL'}($field);
                }

            }

        };

        if ($where = $request->where) {

            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    $spreadParam($query, $key, $value, 'where');
                }
            }
        }

        if ($orWhere = $request->orWhere) {

            if (is_array($orWhere)) {
                $query->where(function ($query) use ($orWhere, $spreadParam) {
                    foreach ($orWhere as $key => $value) {
                        $spreadParam($query, $key, $value, 'orWhere');
                    }
                });
            }
        }

        if ($whereIn = $request->whereIn ?? $request->whereNotIn) {

            if (is_array($whereIn)) {
                foreach ($whereIn as $key => $value) {
                    $spreadParam($query, $key, $value, $request->whereIn ? 'whereIn' : 'whereNotIn');
                }
            }
        }
        if ($whereBetween = $request->whereBetween ?? $request->whereNotBetween) {

            if (is_array($whereBetween)) {
                foreach ($whereBetween as $key => $value) {
                    $spreadParam($query, $key, $value, $request->whereBetween ? 'whereBetween' : 'whereNotBetween');
                }
            }
        }
    }
    public function checkForOrderClause(Request $request, &$query)
    {
        if ($orderBY = $request->orderBy) {

            if (is_array($orderBY)) {
                foreach ($orderBY as $key => $value) {
                    $query->orderBy($key, $value);
                }
            }
        }
    }
}
