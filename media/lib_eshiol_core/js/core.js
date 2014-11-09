/**
 * @copyright	Copyright (C) 2012 Helios Ciancio. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// Only define the eshiol namespace if not defined.			
if (typeof(eshiol) === 'undefined') {
	var eshiol = {};
}

/**
 * Render messages send via JSON
 *
 * @param	object	messages	JavaScript object containing the messages to render
 * @return	void
 */
eshiol.renderMessages = function(messages) {
	var container = document.id('system-message-container');
	var dl = $('system-message');
	if (!dl)
		dl = new Element('dl', {
			id: 'system-message',
			role: 'alert'
		});
	Object.each(messages, function (item, type) {
		var dt = $$('#system-message dt.'+type);
		if (!dt[0])
		{
			dt = new Element('dt', {
				'class': type,
				html: type
			});
			dt.inject(dl);
		}
		var dd = $$('#system-message dd.'+type);
		if (dd[0])
		{
			dd = dd[0];
			var list = $$('#system-message dd.'+type+' ul')[0];
			Array.each(item, function (item, index, object) {
				var li = new Element('li', {
					html: item
				});
				li.inject(list);
			}, this);
		}
		else
		{
			var dd = new Element('dd', {
				'class': type
			});
			dd.addClass('message');
			var list = new Element('ul');
			Array.each(item, function (item, index, object) {
				var li = new Element('li', {
					html: item
				});
				li.inject(list);
			}, this);
		}
		list.inject(dd);
		dd.inject(dl);
	}, this);
	dl.inject(container);
};		

eshiol.dump = function (arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}

eshiol.sendAjax = function(name, id, title, url) 
{
	SqueezeBox.close();
	button = $('toolbar-'+name).getElement('a').innerHTML;
	$('toolbar-'+name).getElement('span').addClass('icon-32-waiting');
	img = $('toolbar-'+name).getElement('span').className;
	Joomla.removeMessages();
	var n = 0;
	var tot = 0;
	var ok = 0;
	for (var i = 0; $('cb'+i) != null; i++)
	{
		if ($('cb'+i).checked)
		{
			var x = new Request.JSON({
				url: Base64.decode(url),
				method: 'post',
				onRequest: function() 
				{
					n++;
					tot++;
					$('toolbar-'+name).getElement('a').innerHTML = '<span class=\''+img+'\'> </span>'+Math.floor(100*(tot-n)/tot)+'%';
				},
				onComplete: function(xhr, status, args)
				{
					n--;
					if (n == 0)
						$('toolbar-'+name).getElement('a').innerHTML = button;
//						$('toolbar-'+name).getElement('span').removeClass('icon-32-waiting');
					else
						$('toolbar-'+name).getElement('a').innerHTML = '<span class=\''+img+'\'> </span>'+Math.floor(100*(tot-n)/tot)+'%';
				},
				onError: function(text, r)
				{
					n--;
					if (n == 0)
						$('toolbar-'+name).getElement('a').innerHTML = button;
//						$('toolbar-'+name).getElement('span').removeClass('icon-32-waiting');					
					else
						$('toolbar-'+name).getElement('a').innerHTML = '<span class=\''+img+'\'> </span>'+Math.floor(100*(tot-n)/tot)+'%';
					if (r.error && r.message)
					{
						alert(r.message);
					}
					if (r.messages)
					{
						eshiol.renderMessages(r.messages);
					}
				},
				onFailure: function(r)
				{
					eshiol.renderMessages({'error':['Unable to connect the server: '+title]});
				},
				onSuccess: function(r) 
				{
					if (r.error && r.message)
					{
						alert(r.message);
					}
					if (r.messages)
					{
						eshiol.renderMessages(r.messages);
					}
				}
			}).send('cid=' + $('cb'+i).value + '&' + name + '_id=' + id);
		}
	}
}