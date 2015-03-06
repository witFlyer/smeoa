<?php
function is_mobile($mobile){
//	return preg_match("/^(?:13\d|14\d|15\d|18[0123456789])-?\d{5}(\d{3}|\*{3})$/", $mobile);

	$reg = "/^(\+86|86|0)?(13\d{9}|(145|147|150|151|152|153|155|156|157|158|159|170|177|180|182|183|185|186|187|188|189)\d{8})$/";
	
	return @preg_match($reg, $mobile);
}

function is_email($email){
//	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	
	$reg = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/";
	
	return preg_match($reg, $email);
}