<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class thread_class extends AWS_MODEL
{
	private $cached_contents = array();

	private function get_cached_content_info($type, $item_id)
	{
		return $this->cached_contents[$type . '_' . $item_id];
	}

	private function cache_content_info($type, $item_id, $content_info)
	{
		$this->cached_contents[$type . '_' . $item_id] = $content_info;
	}

	public function check_thread_type($type)
	{
		switch ($type)
		{
			case 'question':
			case 'article':
			case 'video':
				return true;
		}
		return false;
	}


	public function check_reply_type($type)
	{
		switch ($type)
		{
			case 'question_reply':
			case 'article_reply':
			case 'video_reply':
				return true;
		}
		return false;
	}

	public function check_thread_or_reply_type($type)
	{
		switch ($type)
		{
			case 'question':
			case 'question_reply':
			case 'article':
			case 'article_reply':
			case 'video':
			case 'video_reply':
				return true;
		}
		return false;
	}

	public function check_item_type($type)
	{
		switch ($type)
		{
			case 'question':
			case 'question_reply':
			case 'question_comment':
			case 'question_discussion':
			case 'article':
			case 'article_reply':
			case 'video':
			case 'video_reply':
				return true;
		}
		return false;
	}


	public function get_thread_info_by_id($type, $item_id)
	{
		$item_id = intval($item_id);
		if (!$item_id OR !$this->check_thread_type($type))
		{
			return false;
		}

		$item_info = $this->get_cached_content_info($type, $item_id);
		if (isset($item_info))
		{
			return $item_info;
		}

		$where = ['id', 'eq', $item_id];

		$item_info = $this->fetch_row($type, $where);

		$this->cache_content_info($type, $item_id, $item_info);
		return $item_info;
	}


	public function get_reply_info_by_id($type, $item_id)
	{
		$item_id = intval($item_id);
		if (!$item_id OR !$this->check_reply_type($type))
		{
			return false;
		}

		$item_info = $this->get_cached_content_info($type, $item_id);
		if (isset($item_info))
		{
			return $item_info;
		}

		$where = ['id', 'eq', $item_id];

		$item_info = $this->fetch_row($type, $where);

		$this->cache_content_info($type, $item_id, $item_info);
		return $item_info;
	}


	public function get_thread_or_reply_info_by_id($type, $item_id)
	{
		$item_id = intval($item_id);
		if (!$item_id OR !$this->check_thread_or_reply_type($type))
		{
			return false;
		}

		$item_info = $this->get_cached_content_info($type, $item_id);
		if (isset($item_info))
		{
			return $item_info;
		}

		$where = ['id', 'eq', $item_id];

		$item_info = $this->fetch_row($type, $where);

		$this->cache_content_info($type, $item_id, $item_info);
		return $item_info;
	}

	public function get_item_thread_info_by_id($type, $item_id)
	{
		$item_info = $this->get_thread_or_reply_info_by_id($type, $item_id);
		if (!$item_info)
		{
			return false;
		}

		switch ($type)
		{
			case 'question':
				$item_info['thread_type'] = 'question';
				$item_info['thread_id'] = $item_info['id'];
				return $item_info;

			case 'article':
				$item_info['thread_type'] = 'article';
				$item_info['thread_id'] = $item_info['id'];
				return $item_info;

			case 'video':
				$item_info['thread_type'] = 'video';
				$item_info['thread_id'] = $item_info['id'];
				return $item_info;

			case 'question_reply':
				$thread_info = $this->get_thread_info_by_id('question', $item_info['parent_id']);
				if ($thread_info)
				{
					$thread_info['thread_type'] = 'question';
					$thread_info['thread_id'] = $thread_info['id'];
					return $thread_info;
				}
				return false;

			case 'article_reply':
				$thread_info = $this->get_thread_info_by_id('article', $item_info['parent_id']);
				if ($thread_info)
				{
					$thread_info['thread_type'] = 'article';
					$thread_info['thread_id'] = $thread_info['id'];
					return $thread_info;
				}
				return false;

			case 'video_reply':
				$thread_info = $this->get_thread_info_by_id('video', $item_info['parent_id']);
				if ($thread_info)
				{
					$thread_info['thread_type'] = 'video';
					$thread_info['thread_id'] = $thread_info['id'];
					return $thread_info;
				}
				return false;
		}

		return false;
	}

	public function get_redirect_posts($type, $redirect_id)
	{
		$redirect_id = intval($redirect_id);
		if (!$redirect_id OR !$this->check_thread_type($type))
		{
			return false;
		}
		return $this->fetch_all($type, ['redirect_id', 'eq', $redirect_id]);
	}


	// 不缓存版
	public function get_post_by_id($type, $item_id)
	{
		$item_id = intval($item_id);
		if (!$item_id OR !$this->check_thread_or_reply_type($type))
		{
			return false;
		}

		$where = ['id', 'eq', $item_id];
		return $this->fetch_row($type, $where);
	}

	// 不缓存版
	public function get_posts_by_ids($type, $item_ids)
	{
		if (!$item_ids OR !$this->check_thread_or_reply_type($type))
		{
			return false;
		}

		if ($item_list = $this->fetch_all($type, ['id', 'in', $item_ids, 'i']))
		{
			foreach ($item_list AS $key => $val)
			{
				$result[$val['id']] = $val;
			}
		}

		return $result;
	}

	// 不缓存版
	public function get_threads_by_ids($type, $item_ids)
	{
		if (!$item_ids OR !$this->check_thread_type($type))
		{
			return false;
		}

		if ($item_list = $this->fetch_all($type, ['id', 'in', $item_ids, 'i']))
		{
			foreach ($item_list AS $key => $val)
			{
				$result[$val['id']] = $val;
			}
		}

		return $result;
	}


	public function has_user_relpied_to_thread($thread_type, $thread_id, $uid, $check_scheduled_posts = false)
	{
		$thread_id = intval($thread_id);
		if (!$thread_id)
		{
			return false;
		}

		$uid = intval($uid);

		switch ($thread_type)
		{
			case 'question':
				$reply_type = 'question_reply';
				break;

			case 'article':
				$reply_type = 'article_reply';
				break;

			case 'video':
				$reply_type = 'video_reply';
				break;

			default:
				return false;
		}

		if ($this->fetch_one($reply_type, 'id', [
			['parent_id', 'eq', $thread_id],
			['uid', 'eq', $uid]
		]))
		{
			return 1;
		}

		if ($check_scheduled_posts)
		{
			if ($this->fetch_one('scheduled_posts', 'id', [
				['type', 'eq', $reply_type],
				['parent_id', 'eq', $thread_id],
				['uid', 'eq', $uid]
			]))
			{
				return 2;
			}
		}

		return 0;
	}

	public function update_view_count($item_type, $item_id)
	{
		if (!$this->check_thread_type($item_type))
		{
			return;
		}

		$item_id = intval($item_id);
		$key = 'update_view_count_' . $item_type . '_' . $item_id;
		$now = time();
		$update = false;

		$data = AWS_APP::cache()->get($key);
		if (is_array($data))
		{
			$count = intval($data['count']) + 1;
			$created_at = intval($data['time']);
			$exipres_at = $created_at + 60;
			if ($now >= $exipres_at)
			{
				$update = true;
				$created_at = $now;
			}
		}
		else
		{
			$count = 1;
			$update = true;
			$created_at = $now;
		}

		if ($update)
		{
			$this->update($item_type, '`view_count` = `view_count` + ' . $count, ['id', 'eq', $item_id]);
			$count = 0;
		}

		AWS_APP::cache()->set($key, array(
			'time' => $created_at,
			'count' => $count
		), 3600);
	}

}
