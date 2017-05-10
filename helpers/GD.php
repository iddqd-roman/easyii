<?php
namespace yii\easyii\helpers;

class GD
{
    private $_image;
    private $_mime;
    private $_width;
    private $_height;

    public function __construct($file)
    {
        if (file_exists($file)) {
            $imageData = getimagesize($file);
            $this->_mime = image_type_to_mime_type($imageData[2]);
            $this->_width = $imageData[0];
            $this->_height = $imageData[1];

            switch ($this->_mime) {
                case 'image/jpeg':
                    $this->_image = imagecreatefromjpeg($file);
                    break;
                case 'image/png':
                    $this->_image = imagecreatefrompng($file);
                    break;
                case 'image/gif':
                    $this->_image = imagecreatefromgif($file);
                    break;
            }
        }
    }

    public function resize($width = null, $height = null)
    {
        if(!$this->_image || (!$width && !$height)){
            return false;
        }

        if(!$width)
        {
            if ($this->_height > $height) {
                $ratio = $this->_height / $height;
                $newWidth = round($this->_width / $ratio);
                $newHeight = $height;
            } else {
                $newWidth = $this->_width;
                $newHeight = $this->_height;
            }
        }
        elseif(!$height)
        {
            if ($this->_width > $width) {
                $ratio = $this->_width / $width;
                $newWidth = $width;
                $newHeight = round($this->_height / $ratio);
            } else {
                $newWidth = $this->_width;
                $newHeight = $this->_height;
            }
        }
        else
        {
            $newWidth = $width;
            $newHeight = $height;
        }

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resizedImage, false);

        imagecopyresampled(
            $resizedImage,
            $this->_image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $this->_width,
            $this->_height
        );

        $this->_image = $resizedImage;
    }

    public function resizeAndFill($width = null, $height = null)
    {
        if(!$this->_image || (!$width && !$height)){
            return false;
        }

        if(!$width)
        {
            if ($this->_height > $height) {
                $ratio = $this->_height / $height;
                $newWidth = round($this->_width / $ratio);
                $newHeight = $height;
            } else {
                $newWidth = $this->_width;
                $newHeight = $this->_height;
            }
        }
        elseif(!$height)
        {
            if ($this->_width > $width) {
                $ratio = $this->_width / $width;
                $newWidth = $width;
                $newHeight = round($this->_height / $ratio);
            } else {
                $newWidth = $this->_width;
                $newHeight = $this->_height;
            }
        }
        else
        {
            $newWidth = $width;
            $newHeight = $height;
        }

        $source_aspect_ratio = $this->_width / $this->_height;
        $thumbnail_aspect_ratio = $newWidth / $newHeight;
        if ($this->_width <= $newWidth && $this->_height <= $newHeight) {
            $thumbnail_image_width = $this->_width;
            $thumbnail_image_height = $this->_height;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $thumbnail_image_width = (int) ($newHeight * $source_aspect_ratio);
            $thumbnail_image_height = $newHeight;
        } else {
            $thumbnail_image_width = $newWidth;
            $thumbnail_image_height = (int) ($newWidth / $source_aspect_ratio);
        }
        //Сначала делаем превьюшку по большей стороне с сохранением отношения сторон
        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($thumbnail_gd_image, $this->_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $this->_width, $this->_height);

        //Центруем изображение
        if ($newWidth > $thumbnail_image_width){
            $y = 0;
            $x = ($newWidth - $thumbnail_image_width)/2;
        }else{
            $x = 0;
            /*echo $newHeight;
            echo '<br/>';
            echo $thumbnail_image_height;*/
            $y = ($newHeight - $thumbnail_image_height)/2;
        }
        //Заполняем оставшееся полотно прозрачным цветом
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $thumbnail_gd_image, $x, $y, 0, 0, $thumbnail_image_width, $thumbnail_image_width, $thumbnail_image_width, $thumbnail_image_width);
        //Прозрачный фон
        $transparency = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);//color: white with alpha
        imagefill($resizedImage, 0, 0, $transparency);
        imagesavealpha($resizedImage, true);

        $this->_image = $resizedImage;
    }

    public function cropThumbnail($width, $height)
    {
        if(!$this->_image || !$width || !$height){
            return false;
        }

        $sourceRatio = $this->_width / $this->_height;
        $thumbRatio = $width / $height;

        $newWidth = $this->_width;
        $newHeight = $this->_height;

        if($sourceRatio !== $thumbRatio)
        {
            if($this->_width >= $this->_height){
                if($thumbRatio > 1){
                    $newHeight = $this->_width / $thumbRatio;
                    if($newHeight > $this->_height){
                        $newWidth = $this->_height * $thumbRatio;
                        $newHeight = $this->_height;
                    }
                } elseif($thumbRatio == 1) {
                    $newWidth = $this->_height;
                    $newHeight = $this->_height;
                } else {
                    $newWidth = $this->_height * $thumbRatio;
                }
            } else {
                if($thumbRatio > 1){
                    $newHeight = $this->_width / $thumbRatio;
                } elseif($thumbRatio == 1) {
                    $newWidth = $this->_width;
                    $newHeight = $this->_width;
                } else {
                    $newHeight = $this->_width / $thumbRatio;
                    if($newHeight > $this->_height){
                        $newHeight = $this->_height;
                        $newWidth = $this->_height * $thumbRatio;
                    }
                }
            }
        }

        $resizedImage = imagecreatetruecolor($width, $height);
        imagealphablending($resizedImage, false);

        imagecopyresampled(
            $resizedImage,
            $this->_image,
            0,
            0,
            round(($this->_width - $newWidth) / 2),
            round(($this->_height - $newHeight) / 2),
            $width,
            $height,
            $newWidth,
            $newHeight
        );

        $this->_image = $resizedImage;
    }

    public function save($file, $quality = 90)
    {
        switch($this->_mime) {
            case 'image/jpeg':
                return imagejpeg($this->_image, $file, $quality);
                break;
            case 'image/png':
                imagesavealpha($this->_image, true);
                return imagepng($this->_image, $file);
                break;
            case 'image/gif':
                return imagegif($this->_image, $file);
                break;
        }
        return false;
    }
}