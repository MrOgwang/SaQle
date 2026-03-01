<?php

namespace SaQle\Core\Files;

use SaQle\Core\Files\Storage\{Storage, TempStorage};
use SaQle\Orm\Entities\Field\Types\MediaField;
use RuntimeException;

final class MediaPipeline {

     public static function process_and_store(MediaField $field, TempFileRef $ref,
        Storage $storage,
        string $final_path,
        array $row,
        object $model
    ): void {

        $temp_path = TempStorage::resolve($ref);

        // Step 1: validate media constraints
        self::validate_media($field, $temp_path);

        // Step 2: process media if needed
        $processed_stream = self::process_media(
            $field,
            $temp_path
        );

        // Step 3: store final output
        $storage->put($final_path, $processed_stream);

        if (is_resource($processed_stream)) {
            fclose($processed_stream);
        }
    }

    private static function validate_media(
        MediaField $field,
        string $path
    ): void {

        if (str_starts_with(mime_content_type($path), 'image/')) {
            [$w, $h] = getimagesize($path);

            if ($field->get_max_width() && $w > $field->get_max_width()) {
                throw new RuntimeException("Image width exceeds max_width");
            }

            if ($field->get_min_width() && $w < $field->get_min_width()) {
                throw new RuntimeException("Image width below min_width");
            }

            if ($field->get_aspect_ratio()) {
                [$rw, $rh] = $field->get_aspect_ratio();
                if (abs(($w / $h) - ($rw / $rh)) > 0.01) {
                    throw new RuntimeException("Invalid aspect ratio");
                }
            }
        }

        // Video duration checks hook here later (ffmpeg)
    }

    private static function process_media(
        MediaField $field,
        string $path
    ) {
        // Image-specific processing
        if ($field instanceof \SaQle\Orm\Entities\Field\Types\ImageField) {
            return ImageProcessor::process($field, $path);
        }

        // Video-specific processing hook
        if ($field instanceof \SaQle\Orm\Entities\Field\Types\VideoField) {
            return fopen($path, 'rb'); // placeholder for future ffmpeg pipeline
        }

        // Default: raw stream
        return fopen($path, 'rb');
    }
}