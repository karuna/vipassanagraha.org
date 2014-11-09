/**
 * @version		13.3.6 media/lib_eshiol_core/core.js
 * @copyright	Copyright (C) 2012-2013 Helios Ciancio. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// Only define the eshiol namespace if not defined.			
if (typeof(eshiol) === 'undefined') {
	var eshiol = {};
}

Joomla.JText.strings['SUCCESS'] = 'Message';

/**
 * Render messages send via JSON
 *
 * @param	object	messages	JavaScript object containing the messages to render
 * @return	void
 */
eshiol.renderMessages = function(messages) {
	var container = document.id('system-message-container');

	Object.each(messages, function (item, type) {
		var div = $$('#system-message-container div.alert.alert-'+type);
		if (!div[0])
		{
			div = new Element('div', {
				id: 'system-message',
				'class': 'alert alert-' + type
			});
			div.inject(container);
			var h4 = new Element('h4', {
				'class' : 'alert-heading',
				html: Joomla.JText._(type, type.charAt(0).toUpperCase() + type.slice(1))
			});
			h4.inject(div);
		}
		else
			div = div[0];

//		var divList = new Element('div');
		Array.each(item, function (item, index, object) {
			var p = new Element('p', {
				html: item
			});
//			p.inject(divList);
			p.inject(div);
		}, this);
//		divList.inject(div);
	}, this);
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

eshiol.sendAjax = function(name, id, title, url, token) 
{
	button = $('toolbar-'+name).getElement('button').innerHTML
	img = $('toolbar-'+name).getElement('i').className;
	// TODO: set waiting icon
	
	Joomla.removeMessages();
	var n = 0;
	var tot = 0;
	var ok = 0;
	var url = Base64.decode(url);

	for (var i = 0; $('cb'+i) != null; i++)
	{
		if ($('cb'+i).checked)
		{
			var x = new Request.JSON({
				url: url,
				method: 'post',
				onRequest: function() 
				{
					n++;
					tot++;
					$('toolbar-'+name).getElement('button').innerHTML = '<i class=\''+img+'-waiting\'> </i> '+Math.floor(100*(tot-n)/tot)+'%';
				},
				onComplete: function(xhr, status, args)
				{
					n--;
					if (n == 0) {
						$('toolbar-'+name).getElement('button').innerHTML = button;
					} else
						$('toolbar-'+name).getElement('button').innerHTML = '<i class=\''+img+'-waiting\'> </i> '+Math.floor(100*(tot-n)/tot)+'%';
				},
				onError: function(text, r)
				{
					n--;
					if (n == 0) {
						$('toolbar-'+name).getElement('button').innerHTML = button;
					} else
						$('toolbar-'+name).getElement('button').innerHTML = '<i class=\''+img+'-waiting\'> </i> '+Math.floor(100*(tot-n)/tot)+'%';
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
			}).send('cid=' + $('cb'+i).value + '&' + name + '_id=' + id + '&' + token);
		}
	}
}