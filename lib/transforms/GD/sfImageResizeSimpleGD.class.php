<?php
/*
 * This file is part of the sfImageTransform package.
 * (c) 2007 Stuart Lowes <stuart.lowes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * sfImageResizeSimple class.
 *
 * Resizes image.
 *
 * Resizes the image to the set size.
 *
 * @package sfImageTransform
 * @subpackage transforms
 * @author Stuart Lowes <stuart.lowes@gmail.com>
 * @version SVN: $Id$
 */
class sfImageResizeSimpleGD extends sfImageTransformAbstract
{
  /**
   * Image width.
   * var integer width of the image is to be reized to
  */
  protected $width = 0;

  /**
   * Image height.
   * var integer height of the image is to be reized to
  */
  protected $height = 0;

  /**
   * Construct an sfImageCrop object.
   *
   * @param integer
   * @param integer
   */
  public function __construct($width, $height)
  {
    $this->setWidth($width);
    $this->setHeight($height);
  }

  /**
   * Set the images new width.
   *
   * @param integer
   */
  public function setWidth($width)
  {
    $this->width = (int)$width;
  }

  /**
   * Gets the images new width
   *
   * @return integer
   */
  public function getWidth()
  {
    return $this->width;
  }

  /**
   * Set the images new height.
   *
   * @param integer
   */
  public function setHeight($height)
  {
    $this->height = (int)$height;
  }

  /**
   * Gets the images new height
   *
   * @return integer
   */
  public function getHeight()
  {
    return $this->height;
  }

  /**
   * [img_resizer description]
   * @param  [type] $src     [description]
   * @param  [type] $quality [description]
   * @param  [type] $w       [description]
   * @param  [type] $h       [description]
   * @param  [type] $saveas  [description]
   * @return [type]          [description]
   */
  protected function img_resizer(&$dest_resource,$resource,$w,$h) {

        //list($width,$height)=getimagesize($src);
        $width = imagesx($resource);
        $height = imagesy($resource);
        // check if ratios match
        $_ratio=array($width/$height,$w/$h);
        if ($_ratio[0] != $_ratio[1]) { // crop image

            // find the right scale to use
            $_scale=min((float)($width/$w),(float)($height/$h))/0.9;

            // coords to crop
            $cropX=ceil($width-($_scale*$w));
            $cropY=ceil($height-($_scale*$h));   
           
            // cropped image size
            $cropW=ceil($width-$cropX);
            $cropH=ceil($height-$cropY);
           
            $crop=ImageCreateTrueColor($cropW,$cropH);
            // crop the middle part of the image to fit proportions
            ImageCopy(
                $crop,
                $resource,
                0,
                0,
                (int)($cropX/2),
                (int)($cropY/2),
                $cropW,
                $cropH
            );
        }
       
        // do the thumbnail
        if (isset($crop)) { // been cropped
            ImageCopyResampled(
                $dest_resource,
                $crop,
                0,
                0,
                0,
                0,
                $w,
                $h,
                $cropW,
                $cropH
            );
            $res = 'ratios : '.$_ratio[0].' -> '.$_ratio[1];
            ImageDestroy($crop);
        } else { // ratio match, regular resize
            $res = 'ratio matched';
            ImageCopyResampled(
                $dest_resource,
                $resource,
                0,
                0,
                0,
                0,
                $w,
                $h,
                $width,
                $height
            );
        }
        return $res;
} 


  /**
   * Apply the transform to the sfImage object.
   *
   * @param sfImage
   * @return sfImage
   */
  protected function transform(sfImage $image)
  {
    $resource = $image->getAdapter()->getHolder();

    $w = imagesx($resource);
    $h = imagesy($resource);

    // If the width or height is not valid then enforce the aspect ratio
    if (!is_numeric($this->width) || $this->width < 1)
    {
      $this->width = round(($x / $y) * $this->height);
    }

    else if (!is_numeric($this->height) || $this->height < 1)
    {
      $this->height = round(($y / $x) * $this->width);
    }

    $dest_resource = $image->getAdapter()->getTransparentImage($this->width, $this->height);

    // Preserving transparency for alpha PNGs
    if($image->getMIMEType() == 'image/png')
    {
      imagealphablending($dest_resource, false);
      imagesavealpha($dest_resource, true);
    }

    // Finally do our resizing
    $neww = $this->width;
    $newh = $this->height;
    
    imagecopyresampled($dest_resource,$resource,0, 0, 0, 0, $neww, $newh, $w, $h);

    // crop the image
    //imagecopyresized($dest_resource,$dest_resource, 0, 0, 10, 10, $neww-20, $newh-20, $neww-20, $newh-20);
    //imagecopyresampled($dest_resource,$resource,0, 0, 0, 0, $neww, $newh, $w, $h);



    //$result = self::img_resizer($dest_resource,$resource,$w,$h);
    $info = $w.'/'.$h;
    //$textcolor = imagecolorallocate($dest_resource, 255, 0, 0);
    //imagestring($dest_resource, 5, 0, 0, $info , $textcolor);


    //imagecopyresampled($dest_resource,$resource,0, 0, 0, 0, $this->width, $this->height,$x, $y);
    imagedestroy($resource);

    $image->getAdapter()->setHolder($dest_resource);

    return $image;
  }
}
