<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class AspectRatioValidator extends IValidator {
    public function validate(
        string $field,
        mixed $value,
        mixed $threshold = null,
        array $context = []
    ): ValidationResult {

        // 1️⃣ Validate threshold
        if (
            !is_array($threshold) ||
            count($threshold) !== 2 ||
            !is_numeric($threshold[0]) ||
            !is_numeric($threshold[1]) ||
            $threshold[0] <= 0 ||
            $threshold[1] <= 0
        ) {
            return new ValidationResult(
                false,
                "aspect_ratio rule for {$field} must be [width_ratio, height_ratio]."
            );
        }

        $ratioW = (int)$threshold[0];
        $ratioH = (int)$threshold[1];

        // 2️⃣ Resolve file path
        $filePath = null;

        if (is_array($value) && isset($value['tmp_name'])) {
            $filePath = $value['tmp_name'];
        } elseif (is_string($value) && file_exists($value)) {
            $filePath = $value;
        }

        if (!$filePath || !file_exists($filePath)) {
            return new ValidationResult(
                false,
                "{$field} must be a valid uploaded file."
            );
        }

        // 3️⃣ Detect mime
        $mime = mime_content_type($filePath);

        $width = null;
        $height = null;

        // 4️⃣ Image handling
        if (str_starts_with($mime, 'image/')) {
            $size = @getimagesize($filePath);

            if (!$size) {
                return new ValidationResult(
                    false,
                    "{$field} is not a valid image file."
                );
            }

            $width = $size[0];
            $height = $size[1];
        }

        // 5️⃣ Video handling via ffprobe
        elseif (str_starts_with($mime, 'video/')) {

            $cmd = "ffprobe -v error -select_streams v:0 "
                . "-show_entries stream=width,height "
                . "-of csv=s=x:p=0 "
                . escapeshellarg($filePath);

            $output = shell_exec($cmd);

            if (!$output || !str_contains($output, 'x')) {
                return new ValidationResult(
                    false,
                    "{$field} video dimensions could not be determined."
                );
            }

            [$width, $height] = array_map('intval', explode('x', trim($output)));
        }

        else {
            return new ValidationResult(
                false,
                "{$field} must be an image or video file."
            );
        }

        // 6️⃣ Validate dimensions
        if (!$width || !$height) {
            return new ValidationResult(
                false,
                "{$field} dimensions are invalid."
            );
        }

        // 7️⃣ Compare aspect ratio using cross multiplication
        if ($width * $ratioH !== $height * $ratioW) {
            return new ValidationResult(
                false,
                "{$field} must have an aspect ratio of {$ratioW}:{$ratioH}."
            );
        }

        return new ValidationResult(true, null);
    }
}
