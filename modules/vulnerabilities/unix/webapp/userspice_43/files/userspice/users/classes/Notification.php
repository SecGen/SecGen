<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Notification
{
    private $db, $user_id, $error, $unread, $archive_day_limit;
    private $notifications = array();

    public function __construct($user_id, $all = false, $archive_day_limit = 7) {
        $this->db = DB::getInstance();
        $this->user_id = $user_id;
        $this->archive_day_limit = $archive_day_limit;
        if ($archive_day_limit > 0) $this->archiveOldNotifications($user_id);
        $this->getAllNotifications($all);
    }

    private function getAllNotifications($all) {
        if ($all == false) $where = ' AND is_archived=0';
        else $where = '';
        try {
            $this->notifications = $this->db->query('SELECT * FROM notifications WHERE user_id = ? '.$where.' ORDER BY date_created DESC', array($this->user_id))->results();
            foreach ($this->notifications as $row) {
                if ($row->is_read == 0) $this->unread++;
            }
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }

    public function archiveOldNotifications($user_id) {
        try {
            $this->db->query('UPDATE notifications SET is_archived=1 WHERE user_id = ? AND is_read=1 AND date_created < NOW() - INTERVAL ? DAY', array($user_id, $this->archive_day_limit));
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }

    public function addNotification($message, $user_id = -1) {
        if ($user_id == -1) $user_id = $this->user_id;
        try {
            if ($results = $this->db->query('INSERT INTO notifications (user_id, message, date_created) VALUES (?, ?, ?)', array($user_id, $message,date('Y-m-d H:i:s')))->results()) {
                $this->notifications[] = $results;
                return true;
            }
            else $this->error = 'Unable to query the database.';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }

    public function setRead($notification_id, $read = true) {
        try {
			if ($this->db->query('UPDATE notifications SET is_read = ?, date_read=NOW() WHERE id = ?', array($read, $notification_id))) {
                $this->getAllNotifications($this->user_id);
                $this->unread--;
                return true;
            }
            else $this->error = 'Unable to query the database.';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }

    public function setReadAll($read = true) {
        try {
            if ($this->db->query('UPDATE notifications SET is_read = ?, date_read=NOW() WHERE user_id = ?', array($read, $this->user_id))) {
                $this->getAllNotifications($this->user_id);
                $this->unread = 0;
                return true;
            }
            else $this->error = 'Unable to query the database.';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        return false;
    }

    public function getError() {
        echo $this->error;
        return $this->error;
    }

    public function getNotifications() {
        return $this->notifications;
    }

    public function getCount() {
        return count($this->notifications);
    }

    public function getUnreadCount() {
        return $this->unread;
    }

    public function getLiveUnreadCount() {
		$this->db->query('SELECT is_read FROM notifications WHERE user_id = ?  AND is_read = 0', array($this->user_id));
        return $this->db->count();
    }

    public function getUnreadNotifications() {
		$this->unreadNotifications = $this->db->query('SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 AND is_archived = 0 ORDER BY date_created DESC', array($this->user_id))->results();

        return $this->unreadNotifications;
    }
}
