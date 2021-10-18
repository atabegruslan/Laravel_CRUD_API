<?php

namespace App\Services;

use App\Models\Entry;

class EntryService
{
    public function create($request)
    {
        $imageService = resolve('App\Services\ImageService');

        $entry = Entry::create([
            'place'    => $request->input('place'),
            'comments' => $request->input('comments'),
            'img_url'  => $imageService->makeImage( $request->file('image') )
        ]);

        return $entry;
    }

    public function update($request, $id)
    {
        $entry     = Entry::findOrFail($id);
        $img       = $request->file('image');
        $oldImgUrl = $entry->img_url;

        if ($img)
        {
            $imageService = resolve('App\Services\ImageService');
            $newImgUrl    = $imageService->makeImage($img);
            $imageService->deleteImage($oldImgUrl);
        }

        $entry->update([
            'place'    => $request->input('place'),
            'comments' => $request->input('comments'),
            'user_id'  => $request->input('user_id'),
            'img_url'  => isset($newImgUrl) ? $newImgUrl : $oldImgUrl
        ]);

        return $entry;
    }
}
