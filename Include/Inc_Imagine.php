<?php
class Inc_Imagine {
    public static $jpegQuality = 72;
    public static $pngQuality = 7;
    public static $mode = Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
    public static function getOptions ($customOptions = array()) {
        $options = array(
            'jpeg_quality' => Inc_Imagine::$jpegQuality,
            'png_compression_level' => Inc_Imagine::$pngQuality
        );
        if (empty($customOptions) === FALSE) {
            foreach ($customOptions as $k => $item) {
                $options[$k] = $item;
            }
        }
        return $options;
    }
    public static function resize ($width, $height = NULL, $customOptions = array(), $pathFrom, $pathTo = NULL) {
        global $imagine;
        if (empty($imagine) === FALSE && empty($pathFrom) === FALSE && empty($width) === FALSE) {
            $image = $imagine->open($pathFrom);
            /**
             * if no height => height === scale width
             */
            if ($height === NULL) {
                $ratio = $image->getSize()->getWidth() / $width;
                $height = $image->getSize()->getHeight() / $ratio;
            }
            if ($pathTo === NULL) {
                $pathTo = $pathFrom;
            }
            $size = new Imagine\Image\Box($width, $height);
            try {
                $image->thumbnail($size, Inc_Imagine::$mode)->save($pathTo, Inc_Imagine::getOptions($customOptions));
            } catch (Exception $e) {

            }
        }
    }
    public static function crop ($x1, $y1, $x2, $y2, $customOptions = array(), $pathFrom, $pathTo = NULL) {
        global $imagine;
        if (empty($imagine) === FALSE && empty($pathFrom) === FALSE && empty($x2) === FALSE && empty($y2) === FALSE) {
            $image = $imagine->open($pathFrom);
            if ($pathTo === NULL) {
                $pathTo = $pathFrom;
            }
            try {
                $image->crop(new Imagine\Image\Point($x1, $y1), new Imagine\Image\Box($x2, $y2))->save($pathTo, Inc_Imagine::getOptions($customOptions));
            } catch (Exception $e) {

            }
        }
    }
}