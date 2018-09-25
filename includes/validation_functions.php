 <?php

 $errors = array();
 
function fieldname_as_text($fieldname) {
	$fieldname = str_replace("_", " ", $fieldname);
	$fieldname = ucfirst($fieldname);
	return $fieldname;
} 
 
// * presence
// use trim() so empty spaces don't count
// use === to avoid false positives
// empty() would consider "0" to be empty
function has_presence($value) {
	return isset($value) && $value !== "";
}

function validate_presences($required_fields) {
	global $errors;
	// Expects an assoc. array
	foreach($required_fields as $field) {
		$value = trim($_POST[$field]);
	  if (!has_presence($value)) {
	    $errors[$field] = fieldname_as_text($field) . " can't to blank.";
	  }
	}
}

// * string length
// max length
function has_max_length($value, $max) {
	return strlen($value) <= $max; // Здесь возвращается ложь или истина при сравнении длины строки меньше или равно с максимальным значением. Если длина не равна и не меньше $max, то ложно
}

function validate_max_lengths($fields_with_max_lengths) {
	global $errors;
	// Expects an assoc. array
	foreach($fields_with_max_lengths as $field => $max) { // Для каждого поля в массиве...
		$value = trim($_POST[$field]);  // Очищаем пробелы
	  if (!has_max_length($value, $max)) {  // И если has_max_length ложно (длина больше $max), то выводим текст поля
	    $errors[$field] = fieldname_as_text($field) . " is too long";
	  }
	}
}

// min length

function has_min_length($value, $min) {  // Здесь возвращается ложь или истина при сравнении длины строки с минимальным значением. Если длина меньше $min, то ложно
	return strlen($value) > $min; // Если длина больше, чем $min, то истина
}


function validate_min_lengths($fields_with_min_lengths) {
	global $errors;
	// Expects an assoc. array
	foreach($fields_with_min_lengths as $field => $min) { // Для каждого поля в массиве...
		$value = trim($_POST[$field]);
	  if (!has_min_length($value, $min)) { // И если has_min_length ложно (длина меньше $min), то выводим текст поля
	    $errors[$field] = fieldname_as_text($field) . " is too short";
	  }
	}
}  

// * inclusion in a set
function has_inclusion_in($value, $set) {
	return in_array($value, $set);
}

?>