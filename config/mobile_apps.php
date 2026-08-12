<?php

/*
|--------------------------------------------------------------------------
| Mobile Apps
|--------------------------------------------------------------------------
|
| Central configuration for the "Mobile Apps" admin page.
|
| Update apk_url / video_url here to point at the real files hosted on
| the server. The page and its buttons are generated from this array.
|
| Leave apk_url / video_url empty (or null) to show a "Coming Soon"
| state instead of a broken link.
|
*/

return [
    'apps' => [
        [
            'key'         => 'teacher',
            'name'        => 'School Teacher App',
            'description' => 'Manage classes, attendance, homework, exams and academic activities.',
            'icon'        => 'ti-user-cog',
            'apk_url'     => 'https://expo.dev/artifacts/eas/ZU-sH-6BFxe8ZvK_RGzJlhJ86Gal3UVRyf8hQZsl95c.apk',
            'video_url'   => 'https://paleturquoise-monkey-126256.hostingersite.com/videos/Teacher.mp4',
        ],
        [
            'key'         => 'parent',
            'name'        => 'Parent App',
            'description' => "Stay connected with your child's attendance, fees, academics, notifications and school activities.",
            'icon'        => 'ti-user-heart',
            'apk_url'     => 'https://expo.dev/artifacts/eas/7VUYe0NzpCdC0yNqAVGiRXauJnUxGZaRKCmYyumOyuM.apk',
            'video_url'   => 'https://paleturquoise-monkey-126256.hostingersite.com/videos/Parent.mp4',
        ],
        [
            'key'         => 'student',
            'name'        => 'Student App',
            'description' => 'Access timetable, exams, results, homework, circulars and school updates.',
            'icon'        => 'ti-school',
            'apk_url'     => 'https://expo.dev/artifacts/eas/1K0Kr_SJA3fxqYZ8eBkb-elre_yO7j6jF9a9LN2AClc.apk',
            'video_url'   => 'https://paleturquoise-monkey-126256.hostingersite.com/videos/Student.mp4',
        ],
        [
            'key'         => 'driver',
            'name'        => 'Driver App',
            'description' => 'Manage assigned routes, trips, student attendance and transport activities.',
            'icon'        => 'ti-bus',
            'apk_url'     => 'https://expo.dev/artifacts/eas/qV05lxxPYqWImRf3wx9-4Cp5RFZIq7u_nRQuQKqhkf4.apk',
            'video_url'   => 'https://paleturquoise-monkey-126256.hostingersite.com/videos/Driver.mp4',
        ],
    ],
];
