<?php
// use Illuminate\Support\Facades\Storage;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;

if (!function_exists('uploadFile')) {
    function uploadFile($filename, $cropWidth = null, $cropHeight = null, $folder = 'default', $oldFileName = null, $customFile = null, $forceName = null)

    {

        try {

            $file = $customFile ?? request()->file($filename);

            if (!$file) throw new \Exception("File not found in the request.");



            if ($oldFileName && $oldFileName !== 'default.jpg') {

                $oldFilePath = public_path("uploads/{$folder}/" . $oldFileName);

                if (file_exists($oldFilePath)) @unlink($oldFilePath); 

            }



            // Agar forced naam aaya hai, to wahi use karo ---

            if ($forceName) {

                $newFilename = $forceName;

            } else {

                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                $cleanName = \Illuminate\Support\Str::slug($originalName);

                $newFilename = $cleanName . '.webp';

            }

            

            $directory = public_path("uploads/{$folder}");

            if (!file_exists($directory)) mkdir($directory, 0777, true);



            $image = Image::make($file);

            if ($cropWidth && $cropHeight) $image->fit($cropWidth, $cropHeight);

            $image->encode('webp', 80);

            

            file_put_contents($directory . '/' . $newFilename, (string) $image);

            return $newFilename;



        } catch (\Throwable $th) {

            Log::error('Image Upload Error: ' . $th->getMessage());

            return null;

        }

    }

}

// Ye function plain text lega aur hamesha same hash return karega

if (!function_exists('blind_hash')) {

    function blind_hash($string) {

        if (empty($string)) return null;

        // config('app.key') as salt use kar rahe hain for maximum security

        return hash_hmac('sha256', strtolower(trim($string)), config('app.key'));

    }

}

