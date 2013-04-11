<?php
include_once('config.php');
/**
 * Class that implements a simple cache system.
 * @author Manel Pérez / @manelpm10
 * @version 1.0 
 */
class simpleCache
{
	/**
	 * Indicates whether the cache is enabled.
	 *
	 * @var boolean.
	 */
	private $caching = false;

	/**
	 * Contain the group path.
	 *
	 * @var string.
	 */
	private $group_path = '';

	/**
	 * Contain the file path.
	 *
	 * @var string.
	 */
	private $file_path = '';

	/**
	 * Contain only the filename.
	 *
	 * @var string.
	 */
	private $file_name = '';

	/**
	 * Contain the filename with path.
	 *
	 * @var string.
	 */
	private $file = '';

	/**
	 * Constructor of the class.
	 *
	 */
	public function __construct () {
		$this->caching = true;
	}

	/**
	 * This function returns the caching status (enabled?).
	 *
	 * @return boolean.
	 */
	public function get_status ()
	{
		return $this->caching;
	}

	/**
	 * Function that set a new cache status.
	 *
	 * @param boolean $status Enable or disable the cache.
	 * @return null.
	 */
	public function set_status ( $status )
	{
		$this->caching = $status;
	}

	/**
	 * Function that returns the path to cache dir.
	 *
	 * @param string $id Identificator of the data.
	 * @param string $group_id Identificator of the group.
	 * @return string.
	 */
	private function set_file_routes ( $id, $group_id )
	{
		$key_name	= $this->get_key ( $id );
		$key_group	= $this->get_key ( $group_id );

		$level_one = $key_group;
		$level_two = substr ( $key_name, 0, 4 );

		$this->group_path	= CACHE_DIR . $level_one . '/';
		$this->file_path	= CACHE_DIR . $level_one . '/' . $level_two . '/';
		$this->file_name	= $key_name . CACHE_EXT;
		$this->file			= $this->file_path . $this->file_name;
	}

	/**
	 * Function that returns a hashed key name from an id.
	 *
	 * @param string $id Identificator of the data.
	 * @return hash.
	 */
	private function get_key ( $id )
	{
		return md5 ( $id );
	}

	/**
	 * Function that check the correct integrity between the rescue hash from the cache and the
	 * generated hash.
	 *
	 * @param hash $read_hash Contains the rescue hash from the cache file.
	 * @param string $seralized_data Data to make the local hash to check te integrity.
	 * @return boolean.
	 */
	private function check_integrity ( $read_hash, $serialized_data )
	{
		$hash = md5 ( $serialized_data );

		return ( $read_hash == $hash );
	}

	/**
	 * Function that check the expiration time.
	 *
	 * @param timestamp $expiration_time Contain the rescue time of expiration from the cache file.
	 * @return boolean.
	 */
	private function check_expiration ( $expiration_time )
	{
		return ( time() < $expiration_time );
	}


	/**
	 * Function that save a data into cache system.
	 *
	 * @param string $id Identificator of the data.
	 * @param string $group_id Identificator of the group.
	 * @param array $data The data to be cached.
	 * @param timestamp $ttl The time to expires.
	 * @return mixed int or boolean. Returns the number of bytes that were written to the file, or FALSE on failure.
	 */
	public function save ( $id, $group_id = 'default', $data, $ttl = CACHE_TTL )
	{
		if ( $this->caching )
		{
			$this->set_file_routes( $id, $group_id );
				
			// If the directory don't exists, I create.
			if ( !is_dir ( $this->file_path ) )
			{
				if ( !mkdir ( $this->file_path, 0777, true ) )
				{
					return false;
				}
			}
				
			if ( !is_array ( $data ) && !is_object( $data ) )
			{
				$data = array ( $data );
			}
				
			// Serialize data for caching.
			$data = serialize ( $data );
				
			// I get a hash to check integrity in the future.
			$hash = md5 ( $data );
				
			$meta['expiration_time']	= time () + $ttl;
			$meta['integrity']			= $hash;
			$meta['data']				= $data;
				
			// Serialize meta info to put in a file.
			$data	= serialize ( $meta );
				
			return file_put_contents ( $this->file, $data, LOCK_EX );
		}

		return false;
	}

	/**
	 * If the cache is enabled, the integrity of file is ok and the file is not expired, return the cached file.
	 *
	 * @param string $id Identificator of the data.
	 * @param string $group_id Identificator of the group.
	 * @return mixed String or boolean values.
	 */
	public function get ( $id, $group_id = 'default' )
	{
		if ( $this->caching )
		{
			$this->set_file_routes ( $id, $group_id );
				
			if ( !file_exists ( $this->file ) )
			{
				return false;
			}
				
			$meta	= file_get_contents ( $this->file_path . $this->file_name );
			$meta	= unserialize ( $meta );
				
			$check_expiration	= $this->check_expiration ( $meta['expiration_time'] );
			$check_integrity	= $this->check_integrity ( $meta['integrity'], $meta['data'] );
				
			// Expiration and integrity control.
			if ( $check_expiration && $check_integrity )
			{
				$data = unserialize ( $meta['data'] );

				return $data;
			}
			else
			// Clean the expired or not correct caché.
			{
				$this->remove ( $id, $group_id );

				return false;
			}
		}

		return false;
	}

	/**
	 * Remove cache and meta file identificated by id. If group_level is true, clean all group cache.
	 *
	 * @param string $id Identificator of the data.
	 * @param string $group_id Identificator of the group.
	 * @param boolean $group_level True if I wish delete all cache group. False by default.
	 * @return boolean.
	 */
	public function remove( $id, $group_id = 'default', $group_level = false )
	{
		$this->set_file_routes( $id, $group_id );

		// Don't remove the 'default' group
		if ( $group_level && $group_id != 'default' )
		{
			if ( !$this->group_path || empty ( $this->group_path ) )
			{
				return false;
			}
				
			return $this->del_tree ( $this->group_path );
		}
		// Check that the file exists and delete the file cached folder
		elseif ( $this->file_path && !empty ( $this->file_path ) )
		{
			return $this->del_tree ( $this->file_path );
		}
		else
		{
			return false;
		}
	}

	/**
	 * Delete a dir and all his content.
	 *
	 * @param string $dir Path to the dir to be recursive deleted.
	 * @return boolean .
	 */
	private function del_tree ($dir)
	{
		if ( empty ( $dir ) )
		{
			return false;
		}

		if ( !file_exists ( $dir ) )
		{
			return true;
		}

		if ( !is_dir ( $dir ) || is_link ( $dir ) )
		{
			return unlink ( $dir );
		}

		foreach ( scandir ( $dir ) as $item )
		{
			if ( $item == '.' || $item == '..' )
			{
				continue;
			}
				
			if( is_dir ( $dir . $item ) )
			{
				$this->del_tree ( $dir . $item . '/' );
			}
			else
			{
				unlink ( $dir . $item );
			}
		}

		return rmdir ( $dir );
	}
}
?>