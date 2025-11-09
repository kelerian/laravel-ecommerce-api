<?php

namespace App\Services\Media;

use App\Exceptions\BusinessException;
use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;


class MediaService
{
    private $sizes = [
        'thumb' => [100, 100],
        'small' => [300, 300],
        'medium' => [800, 600]
    ];

    public function uploadFile($model, UploadedFile $file, string $collection)
    {
        $this->validatedFile($file);

        $filename = $this->generatedFileName($file);

        $path = $this->storeFile($file, $model, $collection, $filename);

        $conversions = $this->createResizes($file, $path, $filename);

        return $this->saveToDatabase($model, $file, $collection, $filename, $conversions, $path);
    }


    private function validatedFile(UploadedFile $file)
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!$file->isValid()) {
            throw new BusinessException("File upload failed: " . $file->getErrorMessage());
        }

        if (!in_array($file->getMimeType(),$allowedMimes )){
            throw new BusinessException("Invalid file type");
        }

        if ($file->getSize() > 10485760){
            throw new BusinessException("File is too big");
        }
    }

    private function generatedFileName(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $uniqueName = time().'_'.Str::random(10);
        return $uniqueName.'.'.$extension;
    }

    private function storeFile($file, $model, $collection, $fileName)
    {
        $path = strtolower(class_basename($model)).'/'.$model->id.'/'.$collection;
        $file->storeAs($path, $fileName, 'public');

        return $path;
    }

    private function createResizes($file, $path, $fileName)
    {
        $fileExt = $file->getClientOriginalExtension();
        $conversionsPath = $path . '/conversions';
        Storage::disk('public')->makeDirectory($conversionsPath);
        foreach ($this->sizes as $name => $size) {
            [$width, $height] = $size;

            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            $image->scale($width, $height);
            $encodedImage = $image->encodeByExtension($fileExt);
            $resizeName = $this->getResizeName($fileName, $name);

            Storage::disk('public')->put($conversionsPath .'/' . $resizeName, $encodedImage);
            $conversions[$name] = $conversionsPath.'/'.$resizeName;

        }
        return $conversions;
    }

    private function getResizeName($fileName, $sizeName)
    {
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        return $name . '_' . $sizeName . '.' . $extension;
    }

    private function saveToDatabase($model, $file, $collection, $fileName, $conversions, $path)
    {
        return $model->media()->create([
            'uuid' => Str::uuid(),
            'collection_name' => $collection,
            'name' => $file->getClientOriginalName(),
            'file_name' => $path.'/'.$fileName,
            'mime_type' => $file->getMimeType(),
            'disk' => 'public',
            'size' => $file->getSize(),
            'generated_conversions' => $conversions,
            'custom_properties' => [
                'alt' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'original_name' => $file->getClientOriginalName()
            ]
        ]);
    }

    public function deleteImage(Collection $imageCollection): array
    {
        return DB::transaction(function () use ($imageCollection) {

            $report = [
                'deleted' => ['files' => [], 'media_ids' => []],
                'failed'  => ['files' => [], 'media_ids' => []],
            ];

            foreach ($imageCollection as $image) {
                $result = $this->deleteResize($image);

                if ($result && Storage::disk($image->disk)->delete($image->file_name)) {
                    $report['deleted']['files'][] = $image->file_name;
                    $report['deleted']['media_ids'][] = $image->id;

                    $image->delete();
                } else {
                    $report['failed']['files'][] = $image->file_name;
                    $report['failed']['media_ids'][] = $image->id;
                }
            }

            if (!empty($report['failed']['media_ids'])) {
                Log::channel('image')->info("Failed to delete:\n" . json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return $report;
        });
    }


    protected function deleteResize(Media $image)
    {
        $flag = false;
        $generated = $image->generated_conversions ?? [];

        $this->deleteNullFromGenerated($generated);

        Storage::disk($image->disk)->delete(array_values($generated));

        foreach ($generated as $resizeName => $resize) {
            if(Storage::disk($image->disk)->exists($resize)){
                Log::channel('image')->info("The file at path {$resize} could not be deleted");
                $flag = true;
            } else {
                $generated[$resizeName] = null;
            };
        };

        $image->generated_conversions = $generated;

        return !$flag;
    }

    protected function deleteNullFromGenerated(array &$generated): array
    {
        foreach ($generated as $key => $resize) {
            if(!isset($resize)){
                unset($generated[$key]);
            }
        }
        return $generated;
    }

}
