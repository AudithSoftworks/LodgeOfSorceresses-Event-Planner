<?php

namespace App\Traits\Attendance;

use App\Models\Attendance;

trait IsAttendance
{
    private function parseAttendanceGalleryImages(Attendance $attendance): Attendance
    {
        if (
            $attendance->attributes
            && array_key_exists('gallery_images', $attendance->attributes)
            && in_array('gallery_images', $attendance->getVisible(), true)
        ) {
            return $attendance;
        }

        $ipsApi = app('ips.api');
        $attendance->gallery_image_ids = !empty($attendance->gallery_image_ids)
            ? explode(',', $attendance->gallery_image_ids)
            : [];
        $images = [];
        foreach ($attendance->gallery_image_ids as $galleryImageId) {
            $imageData = $ipsApi->getGalleryImage($galleryImageId);
            if ($imageData !== null) {
                $images[] = $imageData['images'];
            }
        }
        $attendance->setAttribute('gallery_images', $images);
        $attendance->makeVisible(['gallery_images']);
        $attendance->makeHidden(['gallery_image_ids']);

        return $attendance;
    }

    private function parseAttendanceAttendees(Attendance $attendance): Attendance
    {
        if (
            $attendance->attributes
            && array_key_exists('created_by', $attendance->attributes)
            && in_array('created_by', $attendance->getVisible(), true)
        ) {
            return $attendance;
        }

        foreach ($attendance->attendees as $attendee) {
            $attendee->makeHidden(['email', 'created_at', 'updated_at', 'deleted_at', 'attendees']);
            if (($attendance->created_by ?? null) === null && $attendee->getOriginal('pivot_is_author')) {
                $attendance->setAttribute('created_by', $attendee);
                $attendance->makeVisible(['created_by']);
            }
        }

        return $attendance;
    }
}
