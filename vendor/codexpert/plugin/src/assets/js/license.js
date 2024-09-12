jQuery(function($){
	$('.cx-license-btn').click(function(e){
		e.preventDefault()
		var $this = $(this)
		var btn = $this.val()
		var par = $this.parent()
		var key = $('input.key-field', par).val()
		var plugin = $('input[name="plugin_key"]', par).val()
		var operation = $this.attr('name')

		var slug = $this.data('slug')
		var basename = $this.data('basename')
		var name = $this.data('name')

		$this.val('Please wait..')
		$.ajax({
		    url: ajaxurl,
		    type: 'POST',
		    dataType: 'JSON',
		    data: { 'action' : 'license-activator-'+basename, 'operation' : operation, 'plugin' : plugin, 'key' : key, 'product_ref' : name },
		    success:function(ret){
		    	console.log(ret)
		        $this.val(btn)
		        if(ret.status == 1) {
		         $('#license-notice-'+slug).hide()
			        setTimeout(function(e){
			         location.reload()
			        },2000)
		        }
		        $('.'+slug+'-message', par).html(ret.message)
		    }
		})
	})
});