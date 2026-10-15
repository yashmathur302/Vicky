<?php

// use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;

if (!function_exists('uploadFile')) {
    function uploadFile($filename, $cropWidth = null, $cropHeight = null, $folder = 'default', $oldFileName = null, $customFile = null)
    {
        try {
            $file = $customFile ?? request()->file($filename);

            if (!$file) {
                throw new \Exception("File not found in the request.");
            }

            // Purani file direct public folder se delete karna
            if ($oldFileName && $oldFileName !== 'default.jpg') {
                $oldFilePath = public_path("uploads/{$folder}/" . $oldFileName);
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath); // File delete kar dega
                }
            }

            // --- NAYA CODE: Original Name extract karna ---
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            // Spaces aur special characters hatane ke liye Str::slug use karein, aur overwrite se bachne ke liye time append karein
            $cleanName = \Illuminate\Support\Str::slug($originalName);
            $newFilename = $cleanName . '.webp';
            // $newFilename = $cleanName . '_' . time() . '.webp'; // image name with timestamp for making it unique

            // Purani file direct public folder se delete karna
            // if ($oldFileName && $oldFileName !== 'default.jpg') {
            //     $oldFilePath = public_path("uploads/{$folder}/" . $oldFileName);
            //     if (file_exists($oldFilePath)) {
            //         unlink($oldFilePath); // File delete kar dega
            //     }
            // }

            // // Unique naam generate karna
            // $randomString = substr(hash('sha256', uniqid(mt_rand(), true)), 0, 10);
            // $newFilename = $randomString . '.webp';
            
            // Naya folder create karna agar pehle se nahi hai (e.g., public/uploads/users/profile)
            $directory = public_path("uploads/{$folder}");
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Image crop aur WebP encode
            $image = Image::make($file);
            if ($cropWidth && $cropHeight) {
                $image->fit($cropWidth, $cropHeight);
            }
            $image->encode('webp', 80);
            
            // File ko sidha public folder me write karna
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
