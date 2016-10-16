<?php
function regenerateSessions(int $elapse = 20, string $name = 'session_timer') {
	if(isset($_SESSION[$name]) && (time() - $_SESSION[$name]) > $elapse) {
		$_SESSION[$name] = time();
		session_regenerate_id(true);
	} elseif(!isset($_SESSION[$name]))
		$_SESSION[$name] = time();
}