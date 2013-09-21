<?php
/**
 * @version $Id: evecentral.php 210 2009-07-23 18:16:20Z kovalikp $
 * @license GNU/LGPL, see COPYING and COPYING.LESSER
 * This file is part of Ale - PHP API Library for EVE.
 *
 * Ale is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Ale is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Ale.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('ALE_BASE') or define('ALE_BASE', dirname(__FILE__));

require_once ALE_BASE.DIRECTORY_SEPARATOR.'base.php';

class AleZKillboard extends AleBase {

	private $cachedUntil;
	
	protected $default = array(
		'host' => 'https://zkillboard.com/api/',
		'suffix' => '',
		'parserClass' => 'SimpleXMLElement',
		'requestError' => 'throwException',
		'serverError' => 'throwException',
		//'cacheTime' => 300, 
	);

	public function __construct(AleInterfaceRequest $request, AleInterfaceCache $cache = null, array $config = array()) {
		parent::__construct($request, $cache, $config);
	}
	
	/**
	 * Extract cached until time
	 *
	 * @param string $content
	 * @return string
	 */
	protected function getCachedUntil($content) {
		return (string) $this->cachedUntil;
	}
	
	/**
	 * Scans conntent for cachedUntil and error
	 *   
	 * @param string $content
	 * @param int $errorCode
	 * @param string $errorText
	 */
	protected function scanContent($content, &$errorCode, &$errorText) {
		$this->cachedUntil = null;
		$reader = new XMLReader();
		$reader->xml($content);
		while ($reader->read())
		{
			if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == "error")
			{
				// got an error
				$errorText = $reader->readString();
				$errorCode = intval($reader->getAttribute('code'));
				if ($reader->next("cachedUntil"))
				{
					$this->cachedUntil = $reader->readString();
				}
			}
			else if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == "result")
			{
				// no errors, we need to read the cache time though
				if ($reader->next("cachedUntil"))
				{
					$this->cachedUntil = $reader->readString();
				}
			}
		}
		$reader->close();
	}

	/**
	 * Check for server error. Return null, string or object, based on configuration
	 *
	 * @param string $content
	 * @param bool $useCache
	 * @return mixed
	 */
	protected function handleContent($content, &$useCache = true) {
		if (is_null($content)) {
			return null;
		}
		$errorCode = 0;
		$errorText = '';
		$this->scanContent($content, $errorCode, $errorText);

		//if we found an error
		if ($errorCode || $errorText) {
			//we want to update cached until
			$this->cache->updateCachedUntil($this->cachedUntil);
			//but we do not want to cache error, right?
			$useCache = false;
			switch ($this->config['serverError']) {
				case 'returnParsed':
					break;
				case 'returnNull':
					return null;
					break;
				case 'throwException':
				default:
					if (100 <= $errorCode && $errorCode < 200) {
						throw new AleExceptionEVEUserInput($errorText, $errorCode, (string) $this->cachedUntil);
					} elseif (200 <= $errorCode && $errorCode < 300) {
						throw new AleExceptionEVEAuthentication($errorText, $errorCode, (string) $this->cachedUntil);
					} elseif (500 <= $errorCode && $errorCode < 600) {
						throw new AleExceptionEVEServerError($errorText, $errorCode, (string) $this->cachedUntil);
					} else {
						throw new AleExceptionEVEMiscellaneous($errorText, $errorCode, (string) $this->cachedUntil);
					}
			}
		}

		return parent::handleContent($content, $useCache);
	}
}
