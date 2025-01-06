<?php
/**
 * ViraXpress - https://www.viraxpress.com
 *
 * LICENSE AGREEMENT
 *
 * This file is part of the ViraXpress package and is licensed under the ViraXpress license agreement.
 * You can view the full license at:
 * https://www.viraxpress.com/license
 *
 * By utilizing this file, you agree to comply with the terms outlined in the ViraXpress license.
 *
 * DISCLAIMER
 *
 * Modifications to this file are discouraged to ensure seamless upgrades and compatibility with future releases.
 *
 * @category    ViraXpress
 * @package     ViraXpress_CheckoutOptimization
 * @author      ViraXpress
 * @copyright   © 2024 ViraXpress (https://www.viraxpress.com/)
 * @license     https://www.viraxpress.com/license
 */

namespace ViraXpress\CheckoutOptimization\Plugin;

class CsrfValidatorSkip {

	/**
	 * @param \Magento\Framework\App\Request\CsrfValidator $subject
	 * @param \Closure $proceed
	 * @param \Magento\Framework\App\RequestInterface $request
	 * @param \Magento\Framework\App\ActionInterface $action
	 */
	public function aroundValidate(
		$subject,
		\Closure $proceed,
		$request,
		$action
	) {
		$requestUrlCforge = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
		$mystring = $requestUrlCforge;
		$findme = 'customer/ajax';
		$pos = strpos($mystring, $findme);
		if ($pos == true) {
			return; // Skip CSRF check
		}
		$findme = 'customer/';
		$pos = strpos($mystring, $findme);
		if ($pos == true) {
			return; // Skip CSRF check
		}
		if ($request->getModuleName() == 'customer') {
			return; // Skip CSRF check
		}
		$proceed($request, $action);
	}
}
