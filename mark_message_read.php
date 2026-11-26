<?php
require 'config.php';
only_post();
require_staff();
$id = (int)(body()['messageId'] ?? 0);
$s = db()->prepare('UPDATE messages SET is_read = 1 WHERE message_id = ?');
$s->bind_param('i', $id); $s->execute();
out(['success' => true]);
