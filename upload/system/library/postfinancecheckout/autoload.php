<?php
/**
 * PostFinanceCheckout OpenCart
 *
 * This OpenCart module enables to process payments with PostFinanceCheckout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @package Whitelabelshortcut\PostFinanceCheckout
 * @author wallee AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */

/**
 * Autoload function.
 * 
 * Includes the plugin classes as well as PostFinanceCheckout sdk classes.
 *
 * After registering this autoload function with SPL, the following line
 * would cause the function to attempt to load the \PostFinanceCheckout\Sdk\Baz\Qux class
 * from /path/to/project/lib/Baz/Qux.php:
 *
 * new \PostFinanceCheckout\Sdk\Baz\Qux;
 *
 * @param string $class the fully-qualified class name.
 */
spl_autoload_register(
		function ($class){
			$base_dir = DIR_SYSTEM . 'library/postfinancecheckout/';
			
			$sdkPrefix = 'PostFinanceCheckout\\Sdk\\';
			
			$ocPrefix = 'PostFinanceCheckout\\';
			
			// does the class use the namespace prefix?
			$ocLen = strlen($ocPrefix);
			$sdkLen = strlen($sdkPrefix);
			if (strncmp($sdkPrefix, $class, $sdkLen) === 0) {
				// replaces SDK autoloader
				// get the relative class name
				$relative_class = substr($class, $sdkLen);
				
				// replace the namespace prefix with the base directory, replace namespace
				// separators with directory separators in the relative class name, append
				// with .php
				$file = modification($base_dir . 'postfinancecheckout-sdk/lib/' . str_replace('\\', '/', $relative_class) . '.php');
				
				// if the file exists, require it
				 if(file_exists($file) ){
					require_once $file;
				}
				return;
			}
			else if (strncmp($ocPrefix, $class, $ocLen) !== 0) {
				// does not begin with PostFinanceCheckout\
				return;
			}
			
			// get the relative class name
			$relative_class = substr($class, $ocLen);
			
			$last_slash_pos = strrpos($relative_class, '\\');
			
			// get the unqualified class name
			$unqualified = substr($relative_class, $last_slash_pos + 1);
			$cleaned = '';
			// first character should be upper
			for ($i = 0; $i < strlen($unqualified); $i++) {
				if (ctype_upper($unqualified[$i])) {
					$cleaned .= '_';
				}
				$cleaned .= $unqualified[$i];
			}
			// remove _ inserted before first uppercase
			$cleaned = substr($cleaned, 1);
			
			// replace the namespace prefix with the base directory, replace namespace
			// separators with directory separators in the relative class name, append
			// with .php
			$file = $base_dir . strtolower(str_replace('\\', '/', substr($relative_class, 0, $last_slash_pos + 1)) . $cleaned) . '.php';
			
			// if the file exists, require it
			if (file_exists(modification($file))) {
				require_once modification($file);
			}
		});