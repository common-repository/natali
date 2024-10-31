<?php
require_once(ABSPATH . 'wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

class Natali_Files
{
    private static $domain  = 'https://static.natali37.ru/';
    private static $content = '/wp-content/';
    private static $files   = [];

    public static function saveFiles($arFiles, $type = 'previewUrl', $post_id = 0)
    {
        $settings = new Natali_Model_Settings();

        $isSingle = (int)$settings->get('image_color');

        try {
            $localFiles = [];

            foreach ($arFiles as $file) {
                if ($type === 'previewUrl') {
                    $url = str_replace(
                        'https://static.natali37.ru/',
                        'https://static.natali37.ru/media/900/',
                        $file['url']);
                } else {
                    $url = $file['url'];
                }

                if ($isSingle) {
                    if ($file['mainColorImage']) {
                        $localFiles[crc32($url)] = self::getFileID($url, $post_id);
                    }
                } else {
                    $localFiles[crc32($url)] = self::getFileID($url, $post_id);
                }
            }

            return self::$files = $localFiles;
        } catch (\Exception $error) {
            Natali_Log::set($error->getMessage(), $error->getCode());
        }
    }

    static function getFileByUrl($url)
    {
        return self::$files[crc32($url)];
    }

    private static function getFileID($file, $post_id = 0)
    {
        if ($existId = self::findByName($file)) {
            return $existId;
        }

        return media_sideload_image($file, $post_id, null, 'id');
    }

    private static function download($file, $localFile)
    {
        $pathToCopy = $_SERVER['DOCUMENT_ROOT'] . self::$content . $localFile;
        self::createPath($pathToCopy);
        copy($file, $pathToCopy);

        return self::createPostAttachment($pathToCopy);
    }

    private static function getLocalImgUrl($file)
    {
        return str_replace(self::$domain, '', $file);
    }

    private static function createPath(string $pathToCopy)
    {
        $arPath = explode('/', $pathToCopy);
        array_pop($arPath);
        $dir = implode('/', $arPath);

        mkdir($dir, 0755, true);
    }

    private static function createPostAttachment(string $localFile)
    {
        $filetype = wp_check_filetype(basename($localFile), null);

        $wp_upload_dir = wp_upload_dir();
        $attachment = [
            'guid'           => $wp_upload_dir['url'] . '/' . basename($localFile),
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace('/\.[^.]+$/', '', basename($localFile)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        return wp_insert_attachment($attachment, $localFile);
    }

    private static function findByName($localFile)
    {
        $arFile = explode('/', $localFile);

        [$fileName, $fileExt] = explode('.', array_pop($arFile));
        $find = new \WP_Query(
            [
                'post_type' => 'attachment',
                'name'      => $fileName
            ]
        );

        if ($find->have_posts()) {
            $attachments = $find->get_posts();

            foreach ($attachments as $attach) {
                $parsed = parse_url(wp_get_attachment_url($attach->ID));
                $url = dirname($parsed['path']) . '/' . rawurlencode(basename($parsed['path']));
                $isTrue = file_get_contents(get_home_url() . $url);

                if ($isTrue) {
                    return $attach->ID;
                } else {
                    //                    $wp_upload_dir = wp_upload_dir();
                    //                    $updateFile = fopen($parsed['path'], 'w+');
                    //                    fwrite($updateFile, file_get_contents($localFile));
                    //                    fclose($updateFile);
                    //                    return $attach->ID;
                    wp_delete_attachment($attach->ID);
                }
            }
        }

        return false;
    }
}
