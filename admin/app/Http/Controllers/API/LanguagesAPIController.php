<?php

namespace App\Http\Controllers\API;

use App\Models\Translations;
use App\Models\Languages;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguagesAPIController extends Controller
{
    /**
     * Show lists of settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function lists(Request $request)
    {
        try {
            $languages = Languages::select('name','language')->get();
            return $this->sendResponse($languages, __('message_alerts.record_found'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }

    /**
     * Show keysLists of settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function keysLists(Request $request)
    {
        try {
            $response = array();
            $language = setting('preferred_site_language');
            $language = (isset($_GET['language_code']))?$_GET['language_code']:$language;
            $languages = Languages::where('language',$language)->first();
            $translations = Translations::where('group','api')->where('language_id',$languages->id)->get();
            foreach ($translations as $row) {
                $response[$row->key] = $row->value;
            }
            return $this->sendResponse($response, __('message_alerts.record_found'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }
}
