<?php

header('Content-type: image/jpeg');
$phrase = new Gregwar\Captcha\PhraseBuilder;
$phrase = $phrase->build(App::setting('captcha_maxlength'), App::setting('captcha_symbols'));

$builder = new Gregwar\Captcha\CaptchaBuilder($phrase);
$builder->setBackgroundColor(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));
$builder->setMaxOffset(App::setting('captcha_offset'));
$builder->setMaxAngle(App::setting('captcha_angle'));
$builder->setDistortion(App::setting('captcha_distortion'));
$builder->setInterpolation(App::setting('captcha_interpolation'));
$builder->build()->output();

$_SESSION['protect'] = $builder->getPhrase();
