<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Media;

class MediaController extends Controller
{
    public function create(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|mimes:png,jpg,mp4'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        if ($request->hasFile('media')) {
        
            // Get filename with the extension
            $fileNameWithExt = $request->file('media')->getClientOriginalName();

            // Get just filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);

            // Get just ext
            $extension = $request->file('media')->getClientOriginalExtension();

            // File name to store
            $fileNameToStore = $filename.'_'.time().mt_rand( 0, 0xffff ).'.'.$extension;

            // Upload Image
            $path = $request->file('media')->storeAs('public/gallery', $fileNameToStore);
        }
        
        if(isset($fileNameToStore)) {

            if($extension == 'png' || $extension == 'jpg') {
                $fileType = 'image';
            } else if($extension == 'mp4') {
                $fileType = 'video';
            }

            $media = new Media;
            $media->user_id = auth()->user()->id;
            $media->type = $fileType;
            $media->name = $fileNameToStore;
            $media->folder = $request->folderName;
            $media->height = $request->height;
            $media->width = $request->width;
            $media->save();

            return response()->json(200);
        } else {
            return response()->json(500);
        }

    }

    public function load() {
        $user = auth()->user();

        $media = DB::table('media')
            ->where('user_id', $user->id)
            ->select('name', 'folder', 'height', 'width')
            ->groupBy('name', 'folder', 'height', 'width')
            ->get();
        
        // extract folder names
        $folderNames = [];
        foreach($media as $mediaItem) {
            array_push($folderNames, $mediaItem->folder);
        }

        // remove duplicated folder names
        $folderNames = array_unique($folderNames);

        // structure media by folder names
        $structuredMedia = [];
        foreach($folderNames as $folderName) {
            $folder = new \stdClass();
            $folder->name = $folderName;
            $folder->media = [];

            // path to media on server
            $ip = '10.33.20.146';
            $pathToMedia = 'http://'.$ip.':8000/storage/gallery/';

            // push matching media item
            foreach($media as $mediaItem) {
                if($mediaItem->folder == $folderName) {
                    $mediaData = new \stdClass();
                    $mediaData->path = $pathToMedia.$mediaItem->name;
                    $mediaData->height = $mediaItem->height;
                    $mediaData->width = $mediaItem->width;
                    array_push($folder->media, $mediaData);
                }
            }

            // push structured folder to final array
            array_push($structuredMedia, $folder);
        }

        return response()->json($structuredMedia);
    }

    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'selectedMediaNames' => 'required|array|min:1',
            'selectedMediaNames.*' => 'required|string|distinct'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        DB::table('media')
            ->where('user_id', '=', auth()->user()->id)
            ->whereIn('name', $request->selectedMediaNames)
            ->delete();

        return response()->json(200);
    }
}