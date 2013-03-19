;(function($, undefined){
	
	var $skin = $('#prettify__skin'),
		$langs = $('#prettify__langs'),
		$iframe = $skin.find('iframe');
	
	// Resize the iframe to fit the example
	$iframe.load(function(){
		var iframe = this;
		setTimeout(function(){
			$iframe.height($(iframe.contentWindow).outerHeight()).css('overflow','hidden');
			console.log('update');
		}, 100);
        
	});
	
	$skin.find('select').on('change', update_iframe);
	$langs.find('input[type=checkbox]').on('click', update_iframe);
	
	function update_iframe() {
		var url = $iframe.attr('src').split('?')[0] + '?' + build_query_string();
		$iframe.attr('src', url);
	}
	
	function build_query_string() {
		var skin = $skin.find('select').val(),
			$langs = $('#prettify__langs input[type=checkbox]:checked'),
			query = []
			
		if (skin !== 'default') {
			query.push('skin=' + skin);
		}
			
		$langs.each(function(){
			query.push('lang=' + $(this).val());
		});
		
		return query.join('&');
	}
	
	
}(jQuery));