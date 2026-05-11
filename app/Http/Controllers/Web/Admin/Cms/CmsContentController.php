<?php

namespace App\Http\Controllers\Web\Admin\Cms;

use App\Helpers\FileHandle;
use App\Http\Controllers\Controller;
use App\Models\CMS;
use App\Enums\CmsPage;
use App\Enums\CmsSection;
use App\Traits\AdminApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CmsContentController extends Controller
{
    use AdminApiResponse;

    /**
     * Index
     */
    public function index(Request $request): View
    {
        $page = $request->query('page', CmsPage::HOME->value);
        $cmsData = CMS::where('page', $page)->get()->keyBy(function ($item) {
            return $item->section instanceof \App\Enums\CmsSection ? $item->section->value : $item->section;
        });

        return view('web.admin.cms.content.index', [
            'cmsData' => $cmsData,
            'pages' => CmsPage::cases(),
            'currentPage' => $page,
        ]);
    }

    /**
     * Update hero section
     */
    public function updateHero(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,mov,webm,m4v,avi', 'max:51200'], // 50MB
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $cms = CMS::updateOrCreate(
            [
                'page' => CmsPage::HOME,
                'section' => CmsSection::HERO,
            ],
            [
                'title' => $request->title,
                'sub_title' => $request->sub_title,
                'description' => $request->description,
            ]
        );

        if ($request->hasFile('video_file')) {
            if ($cms->video && \Illuminate\Support\Str::startsWith($cms->video, 'uploads/')) {
                FileHandle::fileDelete($cms->video);
            }
            $path = FileHandle::fileUpload($request->file('video_file'), 'cms/videos');
            $cms->update(['video' => $path]);
        }

        return $this->success('Hero section updated successfully.', [
            'cms' => $cms,
        ]);
    }
}
