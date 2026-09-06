<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $data = \App\Models\CMS::where('page', \App\Enums\CmsPage::SERVICES)->get()->keyBy(function($i) {
        return $i->section instanceof \App\Enums\CmsSection ? $i->section->value : $i->section;
    });
    $s = \App\Models\CMS::where('page', \App\Enums\CmsPage::HOME)->where('section', \App\Enums\CmsSection::PARTNERS)->get();
    $data['partners'] = $s;
    $res = \App\Http\Resources\Cms\CmsContentResource::collection($data)->resolve();
    echo "SUCCESS";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine();
}
