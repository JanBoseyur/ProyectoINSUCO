<?php

function versionAsset(string $path): string
{
    $file = $_SERVER['DOCUMENT_ROOT'] . $path;
    return is_file($file) ? $path . '?v=' . filemtime($file) : $path;
}

function versionCSS(string $path): string
{
    return versionAsset($path);
}
