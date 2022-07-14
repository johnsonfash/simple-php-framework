<?php

namespace core;

use enum\graph;

class upload
{

  private static function compressImage($source, $destination, $quality)
  {
    // Get image info 
    $imgInfo = getimagesize($source);
    $mime = $imgInfo['mime'];
    // Create a new image from file 
    switch ($mime) {
      case 'image/jpeg':
        $image = @imagecreatefromjpeg($source);
        imagejpeg($image, $destination, $quality);
        break;
      case 'image/webp':
        $image = @imagecreatefromwebp($source);
        imagejpeg($image, $destination, $quality);
        break;
      case 'image/wbmp':
        $image = @imagecreatefromwbmp($source);
        imagejpeg($image, $destination, $quality);
        break;
      case 'image/png':
        $pngQuality = ($quality - 100) / 11.111111;
        $pngQuality = round(abs($pngQuality));
        $image = @imagecreatefrompng($source);
        imagepng($image, $destination, $pngQuality);
        break;
      case 'image/gif':
        $image = @imagecreatefromgif($source);
        imagegif($image, $destination, $quality);
        break;
      default:
        $image = @imagecreatefromjpeg($source);
        imagejpeg($image, $destination, $quality);
    }


    // Return compressed image 
    return $destination;
  }


  public static function getFiles()
  {
    return isset($_FILES) ? $_FILES : false;
  }


  public static function upload($file, $customName = '', $path = './uploads/')
  {
    $image[graph::error] = false;
    $image[graph::errorMessage] = "";
    $image[graph::path] = "";
    $valid_extensions = array('jpeg', 'jpg', 'png', 'gif'); // valid extensions
    $other_extensions = array('jpeg', 'jpg', 'png', 'gif', 'bmp', 'pdf', 'mp4');
    if ($file['size'] == 0 || empty($file) || !isset($file)) {
      $image[graph::error] = true;
      $image[graph::errorMessage] = "Please upload an image";
    } else {
      if ($file["size"] > 3000000) {
        $image[graph::error] = true;
        $image[graph::errorMessage] = 'Sorry, your file is too large.';
      } else {
        $image[graph::error] = false;
        $img = $file['name'];
        $tmp = $file['tmp_name'];
        $extension = strtolower(pathinfo($img, PATHINFO_EXTENSION));
        $final_image = $customName . rand(10000, 10000000000) . $img;
        $path = $path . strtolower($final_image);
        if (in_array($extension, $valid_extensions)) {
          if (self::compressImage($tmp, $path, 75)) {
            $image[graph::path] = strtolower(graph::uploadFullPath . $final_image);
          } else {
            $image[graph::error] = true;
            $image[graph::errorMessage] = 'File upload failed, please try again!';
          }
        } elseif (in_array($extension, $other_extensions)) {
          if (move_uploaded_file($tmp, $path)) {
            $image[graph::path] = strtolower(graph::uploadFullPath . $final_image);
          } else {
            $image[graph::error] = true;
            $image[graph::errorMessage] = 'File upload failed, please try again!';
          }
        } else {
          $image[graph::error] = true;
          $image[graph::errorMessage] = 'Invalid file type extension (' . $extension . ') ! Please upload (image,pdf).';
        }
      }
    }
    return $image;
  }
}
