function pagination_lazy_loaded(c, href, history) {
	var blocfrag = jQuery(this);
	var content = jQuery(c);
	blocfrag.html(content);

	var a = jQuery('a:first',jQuery(blocfrag)).eq(0);
	if (a.length
		&& a.is('a[name=ajax_ancre]')
		&& jQuery(a.attr('href'),blocfrag).length
	){
		a = a.attr('href');
		jQuery('html,body').animate({scrollTop: jQuery(a).offset().top}, 0);
	}
}
jQuery(function() {
	jQuery('.liste.lazyloading .pagination-items')
		.closest('div.ajaxbloc')
		.attr('data-loaded-callback','pagination_lazy_loaded');
});