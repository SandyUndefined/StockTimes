<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SearchLog extends Model
{
 	protected $table = "search_log";
    protected $dates = ['deleted_at'];
    /**
     * Add Blog
     * @param Array of post data
     * @return category_id 
    */
    public static function addSearchLog($data) {
        try {
            $blog = new self;
            $id=0;
            if($id = $blog->insertGetId($data)) {
                return ['status' => true, 'message' => "Search log added sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in adding search log" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get Search log
     * @param Search data
     * @return array 
    */
    public static function getAllLogs($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            if(isset($search['search_keyword']) && $search['search_keyword'] != ''){
              $keyword = $search['search_keyword'];
              $contact = $contact->where(function($q) use ($keyword){
                    $q->where(DB::raw('LOWER(search_keyword)'), 'like', '%'.strtolower($keyword). '%');
                });
            }
            $data = $contact->orderBy('id','DESC')->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
