<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Translations extends Model
{
    protected $table = "translations";


        /**
     * Get All 
     * @param Search data
     * @return array 
    */
    public static function getTanslations($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if (isset($search['search']) && $search['search'] !='') {
            	if(isset($search['per_page']) && !empty($search['per_page'])){
	                $pagination_no = $search['per_page'];
	            }
	            if(isset($search['key']) && !empty($search['key'] && $search['key'] != '')){
	                $contact = $contact->where(DB::raw('LOWER(keyword)'), 'like', '%'.strtolower($search['key']). '%');
	            }
	            if(isset($search['language_id']) && !empty($search['language_id'] && $search['language_id'] != '')){
	                $contact = $contact->where('language_id',$search['language_id']);
	            }
	            if(isset($search['group']) && !empty($search['group'] && $search['group'] != '')){
	                $contact = $contact->where('group',$search['group']);
	            }
	            $data = $contact->paginate($pagination_no)->appends('per_page', $pagination_no);
            }else{
            	$data = $contact->groupBy('keyword')->paginate($pagination_no)->appends('per_page', $pagination_no);
            }
            
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
