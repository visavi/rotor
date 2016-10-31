<?php

$captcha = new KCAPTCHA();

$_SESSION['protect'] = $captcha->getKeyString();
