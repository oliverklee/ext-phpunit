// Using the Typo3 4.2 AJAX approach.
var tx_phpunit_module1 = {
	thisScript: 'ajax.php',
	ajaxID: 'tx_phpunit_module1_ajax',

	// reloads a part of the page tree (useful when "expand" / "collapse")
	load: function(params) {
		new Ajax.Request('ajax.php', {
			method: 'get',
			parameters: 'ajaxID=tx_phpunit_module1_ajax&PM='+params,
			onComplete: function(xhr) {
				alert(xhr.responseText);
			}.bind(this),
			onT3Error: function(xhr) {
				// if this is not a valid ajax response, the whole page gets refreshed
				alert(xhr.responseText);
			}.bind(this)
		});
	}
};

/*
document.observe("dom:loaded", function() {
	p = $$('h2');
	alert(p.length);
	p[0].style.cursor = 'move';
	p[0].observe('click', tx_phpunit_module1.load);
});
*/