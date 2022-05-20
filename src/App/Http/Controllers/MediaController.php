<?php

namespace Dotlogics\Media\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Dotlogics\Media\App\Models\TempMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Foundation\Validation\ValidatesRequests;

class MediaController extends Controller
{
    use ValidatesRequests;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'file' => 'bail|required|file|max:102400'
        ]);

        $mediaStored = TempMedia::create()->addMediaFromRequest('file')->toMediaCollection('temporary');

        if ( $request->has('accept_only_id') ){
            return $mediaStored->model->id;
        }

        $url = route('media.show', $mediaStored->id);

        // $url = str_replace(rtrim(url('/'), '/'), '', $url);

        return [
            "location" => $url,
            'temp_id' => $mediaStored->model->id,
            'media_id' => $mediaStored->id,
            'media_url' => $url
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param Media $media
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $media)
    {
        $url = with(new Media)::findOrFail($media)->getFullUrl($request->conversion ?? '');

        return redirect($url, 301);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Media $media
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Media $media)
    {
        //
    }

    protected function response($success=true, $message='', $data=[],  $code=200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Media $media
     * @return \Illuminate\Http\Response
     */
    public function destroy($media)
    {
        with(new Media)->delete();
        return $this->response(true, "File Deleted");
    }

    /**
     * @param TempMedia|null $media
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function removeTemp(Request $request)
    {
        $payload = json_decode($request->getContent());

        if ( !$payload instanceof \stdClass || !$payload->temp_id ){
            return $this->response(false, "file Not found", [], 404);
        }

        $tempMedia = TempMedia::find($payload->temp_id);

        if ( !$tempMedia ){
            return $this->response(false, "file Not found", [], 404);
        }

        $tempMedia->delete();
        return $this->response(true, "File Deleted");
    }
}
