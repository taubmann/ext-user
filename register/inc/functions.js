

/*
regex-examples
/^[A-Za-z0-9_@-]{1,30}$/, //only valid chars
/^[A-Za-z]{3,30}$/, //min 3
/^[A-Za-z]{5,30}$/, //min 5
/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i, //email
/^[A-Za-z0-9!@#$%^&*()_<>]{6,20}$/ //password
*/

function showLi (el)
{
	el.slideDown({duration:'slow'});
	if (typeof(el.data('test')) == 'undefined')
	{
		showLi(el.next());
	}
}

$(function ()
{
	$("ul li:first").show();
	
	// reloadable captcha-image
	$('#captcha_img').on('click', function() {
		$(this).attr('src', $(this).attr('src').split('=').shift()+'='+Math.random());
	});
	
	$('ul .inp').on('keyup', function()
	{
		var s = $(this).val();
		var p = $(this).parent();
		var t = p.data('test');
		var u = p.data('unique');
		var go = true;
		
		// test string
		if(t && inputTest[t])
		{
			var re = new RegExp(inputTest[t][0]);
			if (!re.test(s))
			{
				p.find('span.error').show().text(inputTest[t][1]);
				go = false;
			}
			else
			{
				p.find('span.error').hide().text('');
				go = true;
			}
		}
		if(go && p.data('unique'))
		{
			$.post ('test_doubles.php',
			{
				project: projectName,
				conf: configName,
				captcha: $('#captcha').val(),
				field: $(this).attr('id'),
				val: s,
				lang: lang
			},
			function(data)
			{
				if($.trim(data) != 'ok')
				{
					p.find('span.error').show().text(data);
					go = false;
				}
				else
				{
					p.find('span.error').hide().text('');
					go = true;
				}
			});
			
		}
		if(go)
		{
			showLi(p.next());
		}
	});
	
	$('#password').after($('<button type="button" id="gp" >'+jsMsgs['generate_one']+'</button>'));
	
	$('#gp').on('click', function(){
		$('#password').val(GPW.complex(8));
		showLi($('#password').parent().next());
	});
	
	$('.date').each(function()
	{
		var id = $(this).attr('id');
		var da;
		$('datebox_'+id).html('test');
	});
	
	$('.selvarchar').on('change',function()
	{
		$(this).prev().val($(this).val())
	});
	
	
	$('#submit').on('click',function (e)
	{
		e.preventDefault();
		
		var params = {
				project: projectName,
				conf: configName,
				captcha: $('#captcha').val(),
				lang: lang
			};
		
		var addsok = true;
		
		$("ul .inp").each(function()
		{
			var s = $(this).val();
			params[$(this).attr('id')] = s;
			var p = $(this).parent();
			
			// regex-Test
			var t = p.data('test');
			if (t && inputTest[t])
			{
				var re = new RegExp(inputTest[t][0]);
				if(!re.test(s))
				{
					addsok = false;
				}
			}
			
			// looking for unsolved Error-Messages
			var err = p.find('span.error').text();
			if(err.length>0)
			{
				addsok = false;
			}
		});
		
		if (addsok)
		{
			//alert(JSON.stringify(params));return;// test-output
			
			$.post('save.php',
			params,
			function(data)
			{
				if($.trim(data) == 'ok')
				{
					$("#form").show().html('<h3>'+jsMsgs['a_confirmation_mail_is_sent_to_your_address']+'</h3><p>'+jsMsgs['you_can_close_this_window_now']+'</p>');
				}
				else
				{
					$('#submit_error').show().text('Server: '+data);
				}
			});
		}
		else
		{
			$('#submit_error').show().text(jsMsgs['somme_errors_left']);
		}
		return false;
	});

});

// checkbox-change
function change (el, id) {
	$('#'+id).val(($(el).attr('checked')?1:0));
}
