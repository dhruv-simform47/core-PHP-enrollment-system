<?php
echo(phpinfo());
// session_start();
 
// $code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
// $_SESSION['captcha_code'] = $code;
 
// header("Content-type: image/png");
 
// $image = imagecreate(120, 40);  //Creates a blank image:
 
// $bg = imagecolorallocate($image, 255, 255, 255);   //Background color (white)
// $text = imagecolorallocate($image, 0, 0, 0);        //Text color (black)
// $line = imagecolorallocate($image, 200, 200, 200);  //Noise lines (light gray)  
 
// // Adds random noise lines to confuse bots.
// for ($i = 0; $i <5; $i++) {
//     imageline($image, rand(0,20), rand(40,120), rand(100,20), rand(0,40), $line);
// }
 
 
// imagestring($image, 5, 0, 10, $code, $text);
 
// imagepng($image);
// imagedestroy($image);  //Frees memory used by image.
?>
 
 
 
 
<!-- Breakdown:
loop runs 5 times → 5 lines drawn
rand(0,120) → random X position
rand(0,40) → random Y position
imageline() → draws a line between 2 random points -->
 