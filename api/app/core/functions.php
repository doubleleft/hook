<?php

/**
 * Math extensions
 */
function clamp($val, $min, $max) {
	return max($min, min($max, $val));
}
