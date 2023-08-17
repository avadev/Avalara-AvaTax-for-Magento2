/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2018 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define(['jquery', 'domReady!'], function ($) {
    return function () {
		$(document).ready(function() {
			// executes when HTML-Document is loaded and DOM is ready
			$('body').on('click', '.search-taxcode-menu li.item', function (event) {
				var taxCode = $(this).attr('data-tax-code');
				// remove active class from other element and add it on current element
				$('.search-taxcode-menu li.item').removeClass('_active');
				$(this).addClass('_active');

				// update input box value and close dropdown
				$('.avatax-tax-code').val(taxCode);
				$('.autocomplete-results').html('');
			});

			// update input box value and close dropdown on enter
			$('body').on('keyup', '#avatax_code, #tax_avatax_configuration_sales_tax_shipping_tax_code', function (event) {
				if (event.keyCode === 13) {
					$('.autocomplete-results').html('');
				}
			});
		});
    };
});
