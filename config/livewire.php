<?php

/*
 * SECURITY hardening (v0.20.0) — Livewire temporary-upload defaults.
 *
 * The vendor defaults allow ANY file type up to 12MB into the private
 * livewire-tmp directory (disk-fill only — the temp dir is never web-served
 * and preview is signature-protected + attachment-only). Every real upload
 * feature re-validates before persisting to its final disk, but the temp
 * stage itself is now restricted to the union of file types the app
 * legitimately uploads (product photos / digital files / stories / shops /
 * banners / branding), so junk types are rejected at the door.
 */

return [

    'temporary_file_upload' => [
        'disk' => 'local',
        'rules' => ['required', 'file', 'max:12288',
            'mimes:jpg,jpeg,png,webp,ico,pdf,zip,rar,7z,mp4,mp3,wav,ogg,doc,docx,xls,xlsx,ppt,pptx,txt',
        ],
        'prefix' => 'livewire-tmp/',
        'directory' => null,
        'middleware' => null,
    ],

];
