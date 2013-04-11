<?php

include('config.php');
include('simpleCache.php');

/**
 * PHP Class Wrapper for Voobly Public API
 * @author Aldo Agustin Lucchetti / agustin.lucchetti@gmail.com / @agusl88
 * @version 1.0
 * @license GNU Public License v2 - http://www.gnu.org/licenses/gpl-2.0.txt
 */
class vooblyAPI {
    const validation_path = 'validation';
    const user_path = 'user/';
    const ladder_path = 'ladder/';
    const findusers_path = 'findusers/';
    const loobies_path = 'loobies/';
    
    protected $parser;
    protected $cache;

    /**
    * Main constructor
    * @param urlParser $parserInstance
    */
    public function __construct(&$parserInstance) {
        $this->parser = $parserInstance;
        $this->cache = new simpleCache();
    }

    /**
     * Check if the Key is valid
     * @return boolean
     */
    public function validation() {
        $this->parser->setURL(api_path . self::validation_path . '?key=' . API_KEY);
        return($this->parser->parseURL() == 'valid-key');
    }

    /**
    * Lookup the lobbies for a game
    * @param int $game_id
    * @return array
    */
    public function loobies($game_id){
        $this->parser->setURL(api_path . self::loobies_path . $game_id . '?key=' . API_KEY);
        $result = $this->parseString($this->parser->parseURL());
        return $result;
    }

    /**
     * Find Users ID's by Display Name (nicks)
     * @param string $nicks
     * @return array
     */
    public function findUsers($nicks) {
        $result = array();
        $nicks_for_find = array();
        $nicksArry = array();
        foreach ($nicks as $nick) {
            if ($cache_data = $this->cache->get($nick, 'findUsers')) {
                $result[] = $cache_data;
            } else {
                $nicksArry[] = $nick;
            }
        }

        $arrySize = count($nicksArry);
        if ($arrySize > 0) {
            $start = 0;
            $end = ($arrySize <= 30) ? $arrySize : 30;
            $flag = true;

            while ($flag) {
                if ($end > $arrySize - 1) {
                    $end = $arrySize;
                    $flag = false;
                }
                $nicks_for_find = '';
                for ($start; $start < $end; $start++) {
                    $nicks_for_find .= $nicksArry[$start] . ',';
                }
                $nicks_for_find = substr($nicks_for_find, 0, -1); //Delete the last char
                $this->parser->setURL(API_URL . self::findusers_path . $nicks_for_find . '?key=' . API_KEY);
                $query_data = $this->parseString($this->parser->parseURL());
                foreach ($query_data as $player_info) {
                    $this->cache->save($player_info['name'], 'findUsers', $player_info);
                    $result[] = $player_info;
                }
                $end = $end + 30;
            }
        }
        return $result;
    }

    /**
     * Get User profile information by ID
     * @param string $userId
     * @return array
     */
    public function getUserInfo($userID) {
        if ($cache_data = $this->cache->get($userID, 'userInfo')) {
            return $cache_data;
        } else {
            $this->parser->setURL(API_URL . self::user_path . $userID . '?key=' . API_KEY);
            $result = $this->parseString($this->parser->parseURL());
            $this->cache->save($userID, 'userInfo', $result);
            return $result;
        }
    }

    /**
     * Get player ladder information by ID and Ladder
     * @param string $usersId
     * @param int $ladder
     * @return array
     */
    public function getLadderInfo($ids, $ladder) {
        $result = array();
        $ids_for_find = array();
        $idsArry = array();

        foreach ($ids as $id) {
            if ($cache_data = $this->cache->get($id, 'ladderInfo_' . $ladder)) {
                $result[] = $cache_data;
            } else {
                $idsArry[] = $id;
            }
        }

        $arrySize = count($idsArry);
        if ($arrySize > 0) {
            $start = 0;
            $end = ($arrySize <= 30) ? $arrySize : 30;
            $flag = true;

            while ($flag) {
                if ($end > $arrySize - 1) {
                    $end = $arrySize;
                    $flag = false;
                }
                $ids_for_find = '';
                for ($start; $start < $end; $start++) {
                    $ids_for_find .= $idsArry[$start] . ',';
                }
                $ids_for_find = substr($ids_for_find, 0, -1); //Delete the last char
                $this->parser->setURL(API_URL . self::ladder_path . $ladder . '?key=' . API_KEY . '&uidlist=' . $ids_for_find);
                $query_data = $this->parseString($this->parser->parseURL());
                foreach ($query_data as $player_info) {
                    $this->cache->save($player_info['uid'], 'ladderInfo_' . $ladder, $player_info);
                    $result [] = $player_info;
                }
                $end = $end + 30;
            }
        }
        return $result;
    }

    /**
    * Get a "Top X" list form a ladder. Where X is defined by the parameter $limit
    * @param int $ladder
    * @param int $limit 
    * @return array
    */
    public function getTop($ladder, $limit) {
        if ($cache_data = $this->cache->get($ladder . $limit, 'top')) {
            $result = $cache_data;
        } else {
            $this->parser->setURL(API_URL . self::ladder_path . $ladder . '?key=' . API_KEY . '&limit=' . $limit);
            $result = $this->parseString($this->parser->parseURL());
            $this->cache->save($ladder . $limit, 'top', $result);
        }
        return $result;
    }

    /**
     * Auxiliar method for build an array from the string returned by the API querys.
     * @param string $str
     * @return array
     */
    protected function parseString($str) {
        $rows = explode("\n", $str);
        $players_data = array();
        //For to count-1 because the last $row is always empty
        for ($i = 1; $i < count($rows) - 1; $i++) {
            $players_data[] = explode(',', $rows[$i]);
        }
        $keys = explode(',', $rows[0]);
        $aux = array();
        foreach ($players_data as $player) {
            $aux2 = array();
            foreach ($player as $n => $field) {
                $aux2 += array(
                    $keys[$n] => $field
                );
            }
            $aux[] = $aux2;
        }
        return $aux;
    }

}

/**
* Interface for define parsers
*/
interface urlParser {

    /**
    * Setter for the $url var
    * @param string $url
    */
    public function setURL($url);

    /**
    * Fetch the content from the API URL
    * @return string $url_content
    */
    public function parseURL();
}

/**
 * Standar implementation for the urlParser interface
 * @author Aldo Agustin Lucchetti - agustin.lucchetti@gmail.com
 * @throws Timeout Exception
 */
class standarParser implements urlParser {

    protected $url;
    protected $ctx;

    /**
    * Main constructor
    * @param int $timeout
    */
    public function __construct($timeout) {
        $this->ctx= stream_context_create(array('http'=> array(
        'timeout' => $timeout
        )));
    }

    public function setURL($url) {
        $this->url = $url;
    }

    public function parseURL() {
        $url_content = @file_get_contents($this->url,false,$this->ctx);
        if($url_content == NULL){
            throw new Exception('Bad Request or Connection timeout');
        }else{
            return $url_content;
        }
    }

}

?>