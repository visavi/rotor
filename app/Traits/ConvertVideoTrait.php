<?php

declare(strict_types=1);

namespace App\Traits;

use FFMpeg\Exception\RuntimeException;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;

trait ConvertVideoTrait
{
    /**
     * Конвертирует видео
     */
    public function convertVideo(array $file): void
    {
        $isVideo = str_contains($file['mime_type'], 'video/');

        // Обработка видео
        if ($isVideo && config('ffmpeg.enabled')) {
            $config = [
                'ffmpeg.binaries'  => config('ffmpeg.path'),
                'ffprobe.binaries' => config('ffmpeg.ffprobe_path'),
                'ffmpeg.threads'   => config('ffmpeg.threads'),
                'timeout'          => config('ffmpeg.timeout'),
            ];

            // Перекодируем видео в h264
            $ffprobe = FFProbe::create($config);
            $videoInfo = $ffprobe
                ->streams(public_path($file['path']))
                ->videos()
                ->first();

            if (
                $videoInfo
                && $file['extension'] === 'mp4'
                && $videoInfo->get('codec_name') !== 'h264'
            ) {
                try {
                    $ffmpeg = FFMpeg::create($config);
                    $video = $ffmpeg->open(public_path($file['path']));

                    $format = new X264();

                    $video->save($format, public_path($file['path'] . '.convert.mp4'));

                    rename(public_path($file['path'] . '.convert.mp4'), public_path($file['path']));
                } catch (RuntimeException) {
                }
            }
        }
    }
}
