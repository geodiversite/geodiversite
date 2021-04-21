(function($){
	$(function(){

		// Bloc dépliable pour code_embed, pompé sur mediaspip http://zone.spip.org/trac/spip-zone/browser/_squelettes_/mediaspip/mediaspip_core/trunk/javascript/mediaspip_base.js#L9
		$('.code_embed').each(function(){
			var parent = $(this).parents('div.embed');
			if(parent.find('span.title').size() > 0){
				var id_code = parent.find('textarea').attr('id');
				parent.find('.formulaire_embed_code').hide();
				parent.find('span.title').append('<span class="show_code show_code_plus">&nbsp;+</span>').wrapInner('<a class="show_code" href="#'+id_code+'"></a></span>');
			}
			$('.show_code').click(function(){
				var parent = $(this).parents('div.embed');
				if(parent.find('.formulaire_embed_code').is(':hidden')){
					parent.find('.formulaire_embed_code').slideDown("normal");
					parent.find('span.show_code').html('&nbsp;-').removeClass('show_code_plus').addClass('show_code_moins');
				}else{
					parent.find('.formulaire_embed_code').slideUp("normal");
					parent.find('span.show_code').removeClass('show_code_moins').addClass('show_code_plus').html('&nbsp;+');
				}
				return false;
			});
		});
		$('.copypaste').click(function(){
			$(this).focus().select();
		});

	});
})(jQuery);