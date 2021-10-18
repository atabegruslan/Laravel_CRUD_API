<?php

namespace App\Services;

class ImageService
{
    public function makeImage($img)
    {
        $uniquefier       = rand(10000, 99999) . time();
        $final_image_name = $uniquefier . '.' . $img->getClientOriginalExtension();
        $final_image_path = public_path('images');

        $img->move($final_image_path, $final_image_name);

        $src = $final_image_path . '/' . $final_image_name;
        $dst = 'images/' . $final_image_name;

        $this->editImage(env('IMG_RAD'), $src, $dst);

        return url('/images') . '/' . $final_image_name;
    }

    public function editImage($size, $src, $dest, $quality = 80)
    {
        $imgsize = getimagesize($src);
        $width   = $imgsize[0];
        $height  = $imgsize[1];
        $mime    = $imgsize['mime'];
     
        switch ($mime)
        {
            case 'image/gif':
                $image_create = "imagecreatefromgif";
                $image        = "imagegif";

                break;
     
            case 'image/png':
                $image_create = "imagecreatefrompng";
                $image        = "imagepng";
                $quality      = 7;

                break;
     
            case 'image/jpeg':
                $image_create = "imagecreatefromjpeg";
                $image        = "imagejpeg";
                $quality      = 80;

                break;
     
            default:
                return false;

                break;
        }

        $this->makeSquare($size, $src, $dest, $image, $image_create, $width, $height, $quality);
        $this->makeCircle($size, $src, $dest, $image_create);
    }

    public function makeSquare($size, $src, $dest, $image, $image_create, $width, $height, $quality)
    {
        $dst_img = imagecreatetruecolor($size, $size);
        $src_img = $image_create($src);
         
        $width_new  = $height;
        $height_new = $width;

        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if($width_new > $width)
        {
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $size, $size, $width, $height_new);
        }
        else
        {
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $size, $size, $width_new, $height);
        }
        
        $image($dst_img, $dest, $quality);

        if ($src_img) imagedestroy($src_img);
        if ($dst_img) imagedestroy($dst_img);
    }

    public function makeCircle($size, $src, $dest, $image_create)
    {      
        $src_img = $image_create($src);

        $dst_img = imagecreatetruecolor($size, $size);
        imagealphablending($dst_img, true);
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $size, $size, $size, $size);

        $mask        = imagecreatetruecolor($size, $size);
        $transparent = imagecolorallocate($mask, 255, 0, 0);

        imagecolortransparent($mask, $transparent);
        imagefilledellipse($mask, $size / 2, $size / 2, $size, $size, $transparent);
        $red = imagecolorallocate($mask, 0, 0, 0);
        imagecopymerge($dst_img, $mask, 0, 0, 0, 0, $size, $size, 100);
        imagecolortransparent($dst_img, $red);
        imagefill($dst_img, 0, 0, $red);
        
        imagepng($dst_img, $dest); 

        if ($mask) imagedestroy($mask);
        if ($dst_img) imagedestroy($dst_img);       
    }

    public function deleteImage($imgUrl)
    {
        $imageNameParts = explode('/', $imgUrl);
        $imageName      = $imageNameParts[ count($imageNameParts) - 1 ];

        \File::delete('images/' . $imageName);
    }
}
