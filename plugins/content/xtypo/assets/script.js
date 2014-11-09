String.prototype.trim = function()
{
	return this.replace(/^\s+|\s+$/g,"");
}
function codeToList()
{
	var code = document.getElementsByClassName("xtypo_code");
	for (i = 0; i < code.length; i++)
	{
		code[i].innerHTML = "<ol class='xtcode'>" + code[i].innerHTML.replace(/<br>*/g, "<li><span>").replace(/\n/g, "<li><span>").replace(/\t/g, "<li><span>").trim() + "</ol>";
	}
	if (document.getElementsByClassName)
	{
		var code = document.getElementsByClassName("xtcode");
		for (i = 0; i < code.length; i++)
		{	
			var li = code[i].getElementsByTagName("li")
			var cn = "odd";
			for (x = 0; x < li.length; x++)
			{
				li[x].className = cn;
				cn == "odd" ? cn = "even" : cn = "odd"; 
			}
		}
	}	
} 
window.onload = DOMReadyAll;
function DOMReadyAll()
{
	codeToList();
}
