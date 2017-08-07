<?php

header('Content-type: image/jpeg');
$phrase = new Gregwar\Captcha\PhraseBuilder;
$phrase = $phrase->build(Setting::get('captcha_maxlength'), Setting::get('captcha_symbols'));

$builder = new Gregwar\Captcha\CaptchaBuilder($phrase);
$builder->setBackgroundColor(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));
$builder->setMaxOffset(Setting::get('captcha_offset'));
$builder->setMaxAngle(Setting::get('captcha_angle'));
$builder->setDistortion(Setting::get('captcha_distortion'));
$builder->setInterpolation(Setting::get('captcha_interpolation'));
$builder->build()->output();

$_SESSION['protect'] = $builder->getPhrase();
