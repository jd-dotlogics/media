<?php

function to_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    
    $val = rtrim($val, "GgMmKk") * 1;

    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= (1024 * 1024 * 1024); //1073741824
            break;
        case 'm':
            $val *= (1024 * 1024); //1048576
            break;
        case 'k':
            $val *= 1024;
            break;
    }

    return $val;
}

function get_post_max_size_bytes()
{
    return to_bytes(ini_get('post_max_size'));
}

function get_upload_max_filesize_bytes()
{
    return to_bytes(ini_get('upload_max_filesize'));
}

function get_max_file_size_bytes()
{
    $post_max = get_post_max_size_bytes(); // 8mb
    $upload_max = get_upload_max_filesize_bytes(); //5mb
    $media_max = config('media-library.max_file_size'); //10m

    return min($post_max, $upload_max, $media_max);
}